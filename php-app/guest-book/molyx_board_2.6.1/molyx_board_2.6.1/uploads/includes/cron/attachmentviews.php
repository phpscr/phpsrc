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
class cron_attachmentviews
{
	var $class;
	var $cron = '';

	function docron()
	{
		global $DB, $forums, $bboptions;
		if ($bboptions['attachmentviewsdelay']) {
			$forums->lang = $forums->func->load_lang($forums->lang, 'cron');
			if ($attachmentviews = @file(ROOT_PATH . 'cache/cache/attachmentviews.txt')) {
				@unlink(ROOT_PATH . 'cache/cache/attachmentviews.txt');
				$attachmentviews = array_count_values($attachmentviews);
				$countersql = $attachmentids = '';
				foreach ($attachmentviews as $attachmentid => $counter) {
					if ($attachmentid > 0) {
						$countersql .= " WHEN attachmentid = $attachmentid THEN $counter";
						$attachmentids .= ",$attachmentid";
					}
				}
				if (!empty($attachmentids)) {
					$DB->query_unbuffered("UPDATE " . TABLE_PREFIX . "attachment
						SET counter = counter + CASE $countersql ELSE 0 END
						WHERE attachmentid IN (0$attachmentids)");
					$this->class->cronlog($this->cron, $forums->lang['attachmentviews']);
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