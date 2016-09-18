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
class functions_credit
{
	function show_credit($action = "")
	{
		global $forums, $DB;
		$forums->func->check_cache('credit');
		if (count($forums->cache['credit'][$action])) {
			foreach($forums->cache['credit'][$action] AS $tag_name => $credit) {
				$credit_list[] = array($credit['name'], ($credit[$action] > 0 ? "+".$credit[$action] : $credit[$action]));
			}
		}
		return $credit_list;		
	}

	function check_credit_limit($action = "")
	{
		global $bbuserinfo, $forums;
		$forums->func->check_cache('credit');
		if (count($forums->cache['credit']['c_limit'])) {
			foreach($forums->cache['credit']['c_limit'] AS $tag_name => $credit) {
				if ( $bbuserinfo[$tag_name] < intval($credit['c_limit']) AND $forums->cache['credit'][$action][$tag_name][$action] < 0) {
					$error[] = $credit['name'];
				}				
			}
		}
		if (is_array($error) AND !IN_ACP) {
			$forums->func->standard_error("credit_limit_over", 0, implode(", ", $error));
		}
		return TRUE;
	}

	function update_credit($action = "", $userid="", $type="+")
	{
		global $forums, $DB;
		if (!$userid) return;
		if ( is_array( $userid ) ) {
			if ( count($userid) > 0 ) {
				$uid = " IN(".implode(",",$userid).")";
			} else {
				return FALSE;
			}
		} else {
			if ( intval($userid) ) {
				$uid   = "=$userid";
			} else {
				return FALSE;
			}
		}
		$forums->func->check_cache('credit');
		if (count($forums->cache['credit'][$action])) {
			foreach($forums->cache['credit'][$action] AS $tag_name => $credit) {
				if ($type == "+") {
					$f = $credit[$action] > 0 ? "+" : "";
				} else {
					$f = "-";
				}
				$update_expand[] = $tag_name ."=". $tag_name . $f . $credit[ $action ];
			}
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."userexpand SET ".implode(", ", $update_expand)." WHERE id".$uid."" );
		}
	}
}

?>