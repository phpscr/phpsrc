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
class cron_promotion
{
	var $class;
	var $cron = "";

	function docron()
	{
		global $DB, $forums;
		$forums->lang = $forums->func->load_lang($forums->lang, 'cron' );
		$promotions = $DB->query("SELECT u.joindate, u.id, u.membergroupids, u.posts, u.reputation, u.usergroupid, u.name, up.joinusergroupid, up.posts AS jumpposts, up.reputation AS jumpreputation, up.date AS jumpdate, up.type, up.strategy, up.date_sign, up.posts_sign, up.reputation_sign, u.reputation FROM ".TABLE_PREFIX."user u LEFT JOIN ".TABLE_PREFIX."userpromotion up ON (u.usergroupid = up.usergroupid) WHERE u.lastactivity >= ".(time() - 3600*24)."" );
		$primaryupdates = array();
		$secondaryupdates = array();
		$titleupdates = array();
		$primarynames = array();
		$secondarynames = array();
		$titles = array();
		if ($DB->num_rows($promotions)) {
			while ($promotion = $DB->fetch_array($promotions)) {
				if ((strpos(",$promotion[membergroupids],", ",$promotion[joinusergroupid],") === false AND $promotion['type'] == 2) OR ($promotion['usergroupid'] != $promotion['joinusergroupid'] AND $promotion['type'] == 1)) {
					$daysregged = intval((time() - $promotion['joindate']) / 86400);
					$joinusergroupid = $promotion['joinusergroupid'];
					$dojoin = false;
					$posts = $promotion['posts'].$promotion['posts_sign'].$promotion['jumpposts'];
					$reputation = $promotion['reputation'].$promotion['reputation_sign'].$promotion['jumpreputation'];
					$joindate = $daysregged.$promotion['date_sign'].$promotion['jumpdate'];
					eval('$posts = '.$posts.';$reputation = '.$reputation.';$joindate = '.$joindate.';');
					switch ($promotion['strategy']) {
						case '17':
							$dojoin = $posts ? true : false;
							break;
						case '18':
							$dojoin = $joindate ? true : false;
							break;
						case '19':
							$dojoin = $reputation ? true : false;
							break;
						case '1':
							if ($posts AND $joindate AND $reputation) {
								$dojoin = true;
							}
							break;
						case '2':
							if ($posts OR $joindate OR $reputation) {
								$dojoin = true;
							}
							break;
						case '3':
							if (($posts AND $joindate) OR $reputation) {
								$dojoin = true;
							}
							break;
						case '4':
							if (($posts OR $joindate) AND $reputation) {
								$dojoin = true;
							}
							break;
						case '5':
							if ($posts AND ($joindate OR $reputation)) {
								$dojoin = true;
							}
							break;
						case '6':
							if ($posts OR ($joindate AND $reputation)) {
								$dojoin = true;
							}
							break;
						case '7':
							if (($posts OR $reputation) AND $joindate) {
								$dojoin = true;
							}
							break;
						case '8':
							if (($posts AND $reputation) OR $joindate) {
								$dojoin = true;
							}
							break;
					}
					if ($dojoin) {
						if ($promotion['type'] == 1) {
							$primaryupdates[ $joinusergroupid ] .= ",{$promotion['id']}";
							$primarynames[ $joinusergroupid ] .= $primarynames[ $joinusergroupid ] ? ", {$promotion['name']}" : $promotion['name'];
						} else {
							$secondaryupdates[ $joinusergroupid ] .= ",{$promotion['id']}";
							$secondarynames[ $joinusergroupid ] .= $secondarynames[ $joinusergroupid ] ? ", {$promotion['name']}" : $promotion['name'];
						}
					}
				}
			}
		}
		foreach($primaryupdates as $joinusergroupid => $ids) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET usergroupid = $joinusergroupid WHERE id IN (0$ids)");
		}
		foreach($secondaryupdates as $joinusergroupid => $ids) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET membergroupids = IF(membergroupids= '', '$joinusergroupid', CONCAT(membergroupids, ',$joinusergroupid')) WHERE id IN (0$ids)");
		}
		$this->class->cronlog( $this->cron, $forums->lang['updatepromotion'] );
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