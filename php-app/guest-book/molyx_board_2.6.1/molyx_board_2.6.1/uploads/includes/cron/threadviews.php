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
class cron_threadviews
{
	var $class;
	var $cron = '';

	function docron()
	{
		global $DB, $forums, $bboptions;
		if ($bboptions['threadviewsdelay']) {
			$forums->lang = $forums->func->load_lang($forums->lang, 'cron');
			if ($threadviews = @file(ROOT_PATH . 'cache/cache/threadviews.txt')) {
				@unlink(ROOT_PATH . 'cache/cache/threadviews.txt');
				$threadviews = array_count_values($threadviews);
				$viewssql = $tids = '';
				foreach ($threadviews as $tid => $views) {
					if ($tid > 0) {
						$viewssql .= " WHEN tid = $tid THEN $views";
						$tids .= ",$tid";
					}
				}

				if (!empty($tids)) {
					$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "thread
						SET views = views + CASE $viewssql ELSE 0 END
						WHERE tid IN (0$tids)");
					$this->class->cronlog($this->cron, $forums->lang['threadviews']);
				}
			}
		}
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