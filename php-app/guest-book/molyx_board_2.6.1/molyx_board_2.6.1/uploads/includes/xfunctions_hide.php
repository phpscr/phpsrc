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
class hidefunc
{
	function already_bought($buyers=array())
	{
		global $bbuserinfo;
		if ( !$buyers OR !count($buyers) ) {
			return FALSE;
		} elseif ( in_array($bbuserinfo['name'], $buyers) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function canview_hide($hideinfo=array(), $posterid=0, $forumid='')
	{
		global $bbuserinfo;
		if ( !count($hideinfo) OR !$bbuserinfo['id'] ) {
			return FALSE;
		} else	if (!$posterid OR $bbuserinfo['id'] == $posterid OR $bbuserinfo['supermod'] OR is_array( $bbuserinfo['_moderator'][ $forumid ]) ) {
			return TRUE;
		} else	if (($hideinfo['type'] == 1 OR $hideinfo['type'] == 2 OR $hideinfo['type'] == 111) AND $this->already_bought($hideinfo['buyers']) ) {
			return TRUE;
		} else	if ($hideinfo['type'] == 3 AND $bbuserinfo['reputation'] >= $hideinfo['cond']) {
			return TRUE;
		} else	if ($hideinfo['type'] == 4 AND $bbuserinfo['posts'] >= $hideinfo['cond']) {
			return TRUE;
		} else	if ($hideinfo['type'] == 5 AND $bbuserinfo['name'] == $hideinfo['cond']) {
			return TRUE;
		} else	if ($hideinfo['type'] == 11 AND ($bbuserinfo['usergroupid'] == $hideinfo['cond'] OR $bbuserinfo['membergroupids'] == $hideinfo['cond'])) {
			return TRUE;			
		} else	if ($hideinfo['type'] == 999 && $bbuserinfo[$hideinfo['credit_type']] >= $hideinfo['cond']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function canview_hideattach($hideinfo=array(), $posterid=0, $forumid='')
	{
		global $bbuserinfo;
		if ( !$hideinfo OR !count($hideinfo) ) {
			return TRUE;
		} else	if ( !$bbuserinfo['id'] ) {
			return FALSE;
		} else	if ( !$posterid OR $bbuserinfo['id'] == $posterid OR $bbuserinfo['supermod'] OR is_array( $bbuserinfo['_moderator'][ $forumid ]) ) {
			return TRUE;
		} else	if ( ($hideinfo['type'] == 2 OR $hideinfo['type'] == 1) AND $this->already_bought($hideinfo['buyers']) ) {
			return TRUE;
		} else	if ( !preg_match("#\[hide\](.*)\[/hide\]#siU", $row['pagetext']) AND $this->canview_hidecontent AND $hideinfo['type'] != 2 ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function parse_hide_code($row=array(), $forumid='')
	{
		global $bbuserinfo, $forums, $DB;
		if ( !$row OR !count($row) ) {
			return '';
		}
		if ( !$row['hidepost'] ) {
			return $row;
		}	
		$hideinfo = unserialize(stripslashes($row['hidepost']));
		$condition = $hideinfo['cond'];
		$forums->func->check_cache('usergroup');
		$forums->func->check_cache('credit');
		$hidestatus = '';
		if ( $this->canview_hide($hideinfo, $row['userid'], $forumid) ) {
			switch ($hideinfo['type']) {
				case 1:
					$buyers = sprintf( $forums->lang['buyers'], count($hideinfo['buyers']) );
					$requiremoney = sprintf( $forums->lang['requiremoney'], $condition, $forums->cache['banksettings']['bankcurrency'] );
					$hidestatus = $requiremoney.' '.$buyers.' [ <a href="#pid'.$row['pid'].'" onClick="javascript:window.open(\'misc.php?'.$forums->js_sessionurl.'do=whobought&pid='.$row['pid'].'\',\'WhoBought\',\'width=200,height=300,resizable=yes,scrollbars=yes\');">'.$forums->lang['whobought'].'</a> ]';
					break;
				case 3: 
					$requirereputation = sprintf( $forums->lang['requirereputation'], $condition );
					$hidestatus = ' '.$requirereputation;
					break;
				case 4:
					$requirepost = sprintf( $forums->lang['requirepost'], $condition );
					$hidestatus = ' '.$requirepost;
					break;
				case 5:
					$requireuser = sprintf( $forums->lang['requireuser'], $condition );
					$hidestatus = ' '.$requireuser;
					break;
				case 11:
					$requiregroup = sprintf( $forums->lang['requiregroup'], $forums->cache['usergroup'][$condition]['grouptitle'] );
					$hidestatus = ' '.$requiregroup;
					break;
				case 999:
					if (!$forums->cache['credit']['list'][$hideinfo['credit_type']]) {
						$skip_hide = TRUE;
						$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."post SET hidepost = '' WHERE pid='".$row['pid']."'" );
					}
					$hidestatus = sprintf( $forums->lang['requirecredit'] ,$condition ,$forums->cache['credit']['list'][$hideinfo['credit_type']]);
					break;
				default:
					$hidestatus = ' ' . $forums->lang['requirereply'];
					break;
			}
			$hidetop = $forums->lang['hiddencontent'].': '.$hidestatus;
			$repcontent = '\\1';
			$this->canview_hidecontent = TRUE;
		} else {
			switch ( $hideinfo['type'] ) {
				case 1:
					$forums->func->check_cache('banksettings');
					$requiremoney = sprintf( $forums->lang['requiremoney'], $condition, $forums->cache['banksettings']['bankcurrency'] );
					$forums->lang['buyers'] = sprintf( $forums->lang['buyers'], count($hideinfo['buyers']) );
					$hidestatus = $requiremoney.' '.$forums->lang['buyers'].' [ <a href="#pid'.$row['pid'].'" onClick="javascript:window.open(\'misc.php?'.$forums->js_sessionurl.'do=whobought&pid='.$row['pid'].'\',\'WhoBought\',\'width=200,height=300,resizable=yes,scrollbars=yes\');">'.$forums->lang['whobought'].'</a> | <a href="misc.php?'.$forums->sessionurl.'do=buyhidden&amp;tid='.$row['threadid'].'&amp;pid='.$row['pid'].'">'.$forums->lang['wantbuy'].'</a> ]';
					break;
				case 3:
					$requirereputation = sprintf( $forums->lang['requirereputation'], $condition );
					$hidestatus = $requirereputation;
					break;
				case 4:
					$requirepost = sprintf( $forums->lang['requirepost'], $condition );
					$hidestatus = $requirepost;
					break;
				case 5:
					$requireuser = sprintf( $forums->lang['requireuser'], $condition );
					$hidestatus = $requireuser;
					break;
				case 11:
					$requiregroup = sprintf( $forums->lang['requiregroup'], $forums->cache['usergroup'][$condition]['grouptitle'] );
					$hidestatus = $requiregroup;
					break;
				case 999:
					if (!$forums->cache['credit']['list'][$hideinfo['credit_type']]) {
						$skip_hide = TRUE;
						$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."post SET hidepost = '' WHERE pid='".$row['pid']."'" );
					}
					$hidestatus = sprintf( $forums->lang['requirecredit'] ,$condition ,$forums->cache['credit']['list'][$hideinfo['credit_type']]);
					break;
				default:
					$hidestatus = ' ' . $forums->lang['requirereply'];
					break;
			}
			$hidetop = $forums->lang['hidecontent'].': ';
			$repcontent = $hidestatus;
			$this->canview_hidecontent = FALSE;
		}
		$hidebegin = '<div class="hidetop">'.$hidetop.'</div><div class="hidemain">';
		$hideend = '</div>';
		$replacement = $hidebegin.$repcontent.$hideend;	
		if ( $this->canview_hideattach($hideinfo, $row['userid'], $forumid) == TRUE ) {
			$row['canview_hideattach'] = 1;
		} else {
			$row['canview_hideattach'] = 0;
		}
		if ( !preg_match("#\[hide\](.*)\[/hide\]#siU", $row['pagetext']) ) {
			if ( $hideinfo['type'] != 2 ) {
				$row['pagetext'] = "[hide]".$row['pagetext']."[/hide]";
			}
			$row['attachextra'] = 1;
		}	
		if ( $hideinfo['type'] != 2 ) {
			if ($skip_hide) {
				$row['pagetext'] = preg_replace('#\[hide\](.*)\[/hide\]#siU', "\\1", $row['pagetext']);				
			} else {
				$row['pagetext'] = preg_replace('#\[hide\](.*)\[/hide\]#siU', $replacement, $row['pagetext']);
			}
		}
		if ( $hideinfo['type'] == 2 ) {
			$row['hidecond'] = $hideinfo['cond'];
			$row['buyernum'] = count($hideinfo['buyers']);
			$row['onlyattach'] = 1;
		}
		return $row;
	}
	
	function check_hide_condition()
	{
		global $_INPUT, $forums, $bbuserinfo;
	
		if ( !$_INPUT['hidetype'] ) {
			return '';
		}
		$forums->func->check_cache('credit');
		switch ( $_INPUT['hidetype'] ) {
			case 1 :
			case 2 :
			case 3 :
			case 4 :
				$_INPUT['hidecond'] = intval($_INPUT['hidecond']);
				if ( $_INPUT['hidecond'] < 1 ) {
					$errmsg = $forums->lang['notzero'];
				}
				$forums->func->check_cache('banksettings');
				if ( ($_INPUT['hidetype'] == 1 OR $_INPUT['hidetype'] == 2) AND $_INPUT['hidecond'] * 2 > $bbuserinfo['cash'] ) {
					$errmsg = $forums->lang['exceedlimit'];
				}
				$cond = $_INPUT['hidecond'];
				break;
			case 5 :
				if ( !$_INPUT['hidecond'] ) {
					$errmsg = $forums->lang['requirereply'];
				}
				$cond = $_INPUT['hidecond'];
				break;
			case 11  :
				$forums->func->check_cache('usergroup');
				$usergrp = $forums->cache['usergroup'];
				if ( !count($usergrp[$_INPUT['hidegrpid']]) ) {
					$errmsg = $forums->lang['mustusergroup'];
				}
				$cond = $_INPUT['hidegrpid'];
				break;
			case 111: 
				$requirereply = sprintf( $forums->lang['requirereply'], $condition );
				$hidestatus .= ' '.$requirereply;
				break;
			case 999:
				$_INPUT['hidecreditcond'] = intval($_INPUT['hidecreditcond']);
				if ( $_INPUT['hidecreditcond'] < 1 ) {
					$errmsg = $forums->lang['notzero'];
				}
				if (!$forums->cache['credit']['list'][$_INPUT['hidecredit']]) {
					$errmsg = $forums->lang['credittypewrong'];
				}
				$cond = $_INPUT['hidecreditcond'];
				$hideinfo['credit_type'] = $_INPUT['hidecredit'];
				break;
			default  : 
				$errmsg = $forums->lang['conditionerror'];
				$cond = 0;
				break;
		}
		/*if ( ($_INPUT['hidetype'] == 1 OR $_INPUT['hidetype'] == 2) AND $bbuserinfo['mkaccount'] < 1 ) {
			$errmsg = $forums->lang['nousefunction'];
		}*/
		if ( !$errmsg ) {
			$hideinfo['type'] = $_INPUT['hidetype'];
			$hideinfo['cond'] = $cond;
			$hideinfo['attach'] = ($_INPUT['hidetype'] !=2 ) ? 0 : 1;
			$hideinfo['buyers'] = array();
			return $hideinfo;
		} else {
			return $errmsg;
		}
	}
	
	function generate_hidetype_list($newthread=0)
	{
		global $forums;
		$credit_expand = "";
		$hide_list[] = array( "val" => 0, "des" => "=====".$forums->lang['selectcondition']."=====" );
		$hide_list[] = array( "val" => 1, "des" => $forums->lang['viewpostmoney'] );
		$hide_list[] = array( "val" => 2, "des" => $forums->lang['viewattachmoney'] );
		$hide_list[] = array( "val" => 3, "des" => $forums->lang['viewpostreputation'] );
		$hide_list[] = array( "val" => 4, "des" => $forums->lang['viewpostposts'] );
		$hide_list[] = array( "val" => 5, "des" => $forums->lang['onlyuser'] );
		$hide_list[] = array( "val" => 11, "des" => $forums->lang['onlyusergroup'] );
		$hide_list[] = array( "val" => 111, "des" => $forums->lang['viewrequirereply'] );

		$forums->func->check_cache('credit');
		if (count($forums->cache['credit'])) {
			$hide_list[] = array( "val" => 999, "des" => $forums->lang['viewrequirecredit'] );
		}

		return $hide_list;
	}
}

?>