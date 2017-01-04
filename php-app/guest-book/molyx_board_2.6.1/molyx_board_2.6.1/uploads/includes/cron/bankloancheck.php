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
class cron_bankloancheck
{
	var $class;
	var $cron = "";

	function docron()
	{
		global $DB, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );
		$overloanusers = $DB->query("SELECT u.id, u.name, u.usergroupid, u.cash, u.bank, u.reputation, u.mkaccount, e.loanreturn, e.loanamount, e.loaninterest
			FROM ".TABLE_PREFIX."userextra e
			LEFT JOIN ".TABLE_PREFIX."user u ON (e.id = u.id) WHERE e.loanamount > 0 AND e.loanreturn < ".time());
		$returnoks = array();
		$selliusers = array();
		$pbusers = array();
		$forums->func->check_cache('banksettings');
		$banksettings = $forums->cache['banksettings'];
		require_once(ROOT_PATH.'includes/xfunctions_bank.php');
		$this->bankfunc = new bankfunc();
		while ( $thisuser = $DB->fetch_array($overloanusers) ) {
			$moneytoreturn = $this->bankfunc->calculate_interest($thisuser['loanamount'], 1000+$thisuser['loaninterest']);
			// 尝试用资金偿还
			$thisuser['money'] = $thisuser['cash']+$thisuser['bank'];
			$this->bankfunc->desc = $forums->lang['moneytoreturn'];
			$this->bankfunc->add_money($thisuser, -1*$moneytoreturn, 1);
			$moneytoreturn = $moneytoreturn - $thisuser['money'];
			if ( $moneytoreturn < 1 ) {
				$returnoks[] = $thisuser['id'];
			}
			$userinfo[$thisuser['id']]=$thisuser;
			// 有积分的就折钱
			if ( $moneytoreturn > 0 && $banksettings['bankrepsellprice'] && $thisuser['reputation'] )
			{
				$reptosell = 0;
				for ( $i=0; $i < $thisuser['reputation']; $i++ ) {
					$reptosell++;
					if ( ($reptosell*$banksettings['bankrepsellprice']/2) >= $moneytoreturn ) {
						break;
					}
				}
				$repvalue = $reptosell*$banksettings['bankrepsellprice']/2;
				$moneytoreturn = $moneytoreturn - $repvalue;
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET reputation = reputation-".$reptosell." WHERE id = ".$thisuser['id']." LIMIT 1");
			}
			if ( $moneytoreturn < 1 ) {
				$selliusers[] = $thisuser['id'];
			}
			// 只好破产封帐号了
			if ( $moneytoreturn > 0 && $banksettings['bankpbban'] ) {
				$pbusers[] = $thisuser['id'];
			}
		}

		if ( count($pbusers) ) {
			foreach ($pbusers AS $id) {
				$banstring = time().":".($banksettings['bankpbban']*86400).":".$banksettings['bankpbban'].":d:".$userinfo[$id]['usergroupid'].":banksystem";
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET mkaccount = -1, usergroupid = 5, liftban = '".$banstring."' WHERE id=".$id);
			}
		}
		$tmparray = array_merge($returnoks, $pbusers);
		if ( count($tmparray) ) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET reputation = reputation-".$banksettings['bankpbreppanish']." WHERE id IN (".implode(",", $tmparray).")");
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."userextra SET loanreturn=0, loanamount=0, loaninterest=0 WHERE id IN (".implode(",", $tmparray).")");
		}
		$this->class->cronlog( $this->cron, $forums->lang['bankloan'] );
	}

	function register_class(&$class)
	{
		$this->class = $class;
	}

	function pass_cron( $this_cron )
	{
		$this->cron = $this_cron;
	}
}

?>