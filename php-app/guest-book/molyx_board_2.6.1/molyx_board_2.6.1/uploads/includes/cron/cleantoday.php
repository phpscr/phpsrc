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
class cron_cleantoday
{
	var $cron = "";

	function docron()
	{
		global $forums, $DB;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );
		unset($forums->cache['stats']);
		$forums->func->check_cache('stats');
		$stats = $DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."forum SET todaypost=0" );
		require_once( ROOT_PATH.'includes/adminfunctions_cache.php' );		
		$this->cache = new adminfunctions_cache();
		$this->cache->stats_recache('corntodaypost');
		$this->cache->ad_recache();
		$this->cache->forum_recache();
		$this->class->cronlog($this->cron, $forums->lang['cleantoday']);
	}

	function register_class($class)
	{
		$this->class = $class;
	}

	function pass_cron( $this_cron )
	{
		$this->cron = $this_cron;
	}
}
?>