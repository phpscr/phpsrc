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
class cron_refreshjs
{
	var $cron = "";

	function docron()
	{
		global $DB, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );
		require_once( ROOT_PATH.'includes/adminfunctions_javascript.php' );
		$this->lib = new adminfunctions_javascript();
		$cron_time = $DB->query_first("SELECT nextrun FROM ".TABLE_PREFIX."cron WHERE filename = 'refreshjs.php'");
		$this_jss = $DB->query("SELECT * FROM ".TABLE_PREFIX."javascript WHERE nextrun <= ".$cron_time['nextrun']." AND refresh != 0");
		if ($DB->num_rows($this_jss)) {
			while ($js = $DB->fetch_array($this_jss)) {
				$this->lib->createjs($js, 1);
				if ($js['refresh'] > 0) {
					$nextrun = time() + 60;
				}
				if ($cron_time['nextrun'] > $nextrun) {
					$cron_time['nextrun'] = $nextrun;
					$update_db = TRUE;
				}
				if (!$next_do_cron) {
					$next_do_cron = $nextrun;
				}
				$next_do_cron = ($nextrun < $next_do_cron) ? $nextrun : $next_do_cron;
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."javascript SET nextrun='".$next_do_cron."' WHERE id = ".$js['id']."");
			}
			if ($update_db) {
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."cron SET nextrun='".$cron_time['nextrun']."' WHERE filename = 'refreshjs.php'");
			}
			if ($forums->cache['cron'] > $cron_time['nextrun'] ) {
				$forums->func->update_cache( array( 'name' => 'cron', 'value' => $next_do_cron, 'array' => 0 ) );
			}
		} else {
			$new_js = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."javascript WHERE nextrun > ".$cron_time['nextrun']." AND refresh != 0 ORDER BY nextrun LIMIT 0, 1");
			if ($new_js) {
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."cron SET nextrun='".$new_js['nextrun']."' WHERE filename = 'refreshjs.php'");
			}
		}
		$this->class->cronlog($this->cron, $forums->lang['refreshjs']);
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