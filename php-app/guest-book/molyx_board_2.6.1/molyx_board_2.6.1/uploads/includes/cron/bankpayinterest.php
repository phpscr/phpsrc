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
class cron_bankpayinterest
{
	var $class;
	var $cron = "";

	function docron()
	{
		global $DB, $forums, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );
		$forums->func->check_cache('banksettings');
		$interestrate = $forums->cache['banksettings']['bankinterest'];
		$ids = $DB->query("SELECT u.id, e.loanamount
				   FROM ".TABLE_PREFIX."user u
				   LEFT JOIN ".TABLE_PREFIX."userextra e ON (e.id = u.id) WHERE u.bank > 0 AND u.mkaccount > 0");
		if ($DB->num_rows($ids)) {
			$idtopay = array();
			while ($id = $DB->fetch_array($ids)) {
				if ($id['loanamount'] < 1)
				$idtopay[] = $id['id'];
			}
			if (count($idtopay) > 0) {
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET bank = bank*(1000+".$interestrate.")/1000 WHERE id IN (".implode(",", $idtopay).")");
			}
		}
		$this->class->cronlog( $this->cron, $forums->lang['payinterest'] );
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