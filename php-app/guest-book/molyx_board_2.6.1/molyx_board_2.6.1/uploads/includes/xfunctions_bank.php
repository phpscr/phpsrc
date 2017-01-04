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
class bankfunc
{
	var $dorw = '';
	var $desc = '';
	var $trdesc = '';
	var $fromCorB = 0;
	var $tarCorB = 0;
	var $meextra=0;
	var $tarextra=0;

	function get_userinfo($userid=0)
	{
		global $DB;
		if ( !$userid )
			return;
		if ( is_array($userid) AND count($userid) ) {
			$userinfo = $userid;
			if ( $userinfo['id'] < 1 )
				return;
		} else {
			$userinfo = $DB->query_first("SELECT u.id, u.bank, u.cash, u.mkaccount 
						      FROM ".TABLE_PREFIX."user u
						      WHERE u.id = ".intval($userid));
		}
		if ( !$userinfo OR !count($userinfo) OR !$userinfo['id'] )
			return;
		return $userinfo;
	}
	
	function patch_bankinfo($user=array())
	{
		global $DB, $bbuserinfo;
		$userinfo = $user ? $user : $bbuserinfo;
		if ( !$userinfo['id'] ) {
			return $userinfo;
		}
		$infoarray = $DB->query_first("SELECT id, loanamount, loanreturn, loaninterest FROM ".TABLE_PREFIX."userextra WHERE id = ".$userinfo['id']);
		if ( !$infoarray['id']) {
			return $userinfo;
		}
		$userinfo = array_merge($userinfo, $infoarray);
		return $userinfo;
	}
	
	function add_money($userid=0, $num=0, $CorB=0, $mkaccount=FALSE)
	{
		global $DB;
		if ( !$userid ) {
			return 0;
		}
		$userinfo = $this->get_userinfo($userid);
		if ( $CorB AND (!$userinfo['mkaccount'] OR $userinfo['mkaccount'] < 1) ) {
			return 0;
		}
		if ( $CorB == 1 ) {
			$bankleft = $userinfo['bank']+$num;
			$cashleft = $userinfo['cash'];
			if ( $bankleft < 0 AND ($cashleft+$bankleft) >= 0) {
				$cashleft = $cashleft+$bankleft;
				$bankleft = 0;
			}
			if ( $cashleft < 0 ) {
				$cashleft = 0;
			}
		} else {
			$bankleft = $userinfo['bank'];
			$cashleft = $userinfo['cash']+$num;
			if ( $cashleft < 0 AND ($bankleft+$cashleft) >= 0 ) {
				$bankleft = $bankleft+$cashleft;
				$cashleft = 0;
			}
			if ( $bankleft < 0 ) {
				$bankleft = 0;
			}
		}
		$sql_mkaccount = "";
		if ( $mkaccount == TRUE ) {
			$sql_mkaccount = "', mkaccount='".TIMENOW;
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET cash='".$cashleft."', bank='".$bankleft.$sql_mkaccount."' WHERE id='".$userinfo['id']."'");
		if ($this->desc) {
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."banklog (touserid, action, dateline) VALUES (".$userinfo['id'].", '".addslashes($this->desc)."', ".TIMENOW.")");
		}
		return 1;
	}
	
	function bank_transfer_money($num=0)
	{
		global $DB, $bbuserinfo, $forums;
		$num = intval($num);
		if ( !$bbuserinfo['id'] OR $num < 1 ) {
			return 0;
		}
		if ( $this->dorw != "d" AND $this->dorw != "w" ) {
			return 0;
		}
		if ( $bbuserinfo['mkaccount'] < 1 ) {
			return 0;
		}
		if ( $this->dorw == "d" ) {
			$cashleft = $bbuserinfo['cash'] - $num;
			$bankleft = $bbuserinfo['bank'] + $num;
			$bank_log = sprintf($forums->lang['deposit_log'], $bbuserinfo['name'], $num);
		} else {
			$cashleft = $bbuserinfo['cash'] + $num;
			$bankleft = $bbuserinfo['bank'] - $num;
			$bank_log = sprintf($forums->lang['payout_log'], $bbuserinfo['name'], $num);
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET cash=".$cashleft.", bank=".$bankleft." WHERE id='".$bbuserinfo['id']."'");
		$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."banklog (fromuserid, touserid, action, dateline) VALUES (".$bbuserinfo['id'].", ".$bbuserinfo['id'].", '".addslashes($bank_log)."', ".TIMENOW.")");
		return 1;
	}
	
	function calculate_interest($num=0, $rate=0)
	{
		$result = $num*$rate/1000;
		$result = round($result);
		return $result;
	}
	
	// $me $tar 都是array
	// $num 转移金额，必须大于0
	// $this->meextra 出款方额外金钱操作，比如利息、折扣等，可负数
	// $this->tarextra 收款方额外金钱操作
	// $this->fromCorB 出款方式 0=现金 1=银行
	// $this->tarCorB 收款方式
	function user_transfer_money($tar=array(), $num=0)
	{
		global $DB, $bbuserinfo;
		$num = intval($num);
		if ( !$bbuserinfo['id'] OR !$tar['id'] OR $num < 1 ) {
			return 0;
		}
		if ( !$this->fromCorB ) {
			$mymoneytype = "cash";
		} else {
			$mymoneytype = "bank";
		}
		$mymoneyleft = $bbuserinfo[$mymoneytype] - $num;
		if ( !$this->tarCorB ) {
			$tarmoneytype = "cash";
		} else {
			$tarmoneytype = "bank";
		}
		$tarmoneyleft = $tar[$tarmoneytype] + $num;
		if ( $this->meextra ) {
			$mymoneyleft += $meextra;
		}
		if ( $this->tarextra ) {
			$tarmoneyleft += $tarextra;
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ".$mymoneytype." = ".$mymoneyleft." WHERE id = ".$bbuserinfo['id']."");
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ".$tarmoneytype." = ".$tarmoneyleft." WHERE id = ".$tar['id']."");
		if ( $this->trdesc AND strlen($this->trdesc) > 0 ) {
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."banklog (fromuserid, touserid, action, dateline) VALUES (".$bbuserinfo['id'].", ".$tar['id'].", '".addslashes($this->trdesc)."', ".TIMENOW.")");
		}
		return 1;
	}
}

?>