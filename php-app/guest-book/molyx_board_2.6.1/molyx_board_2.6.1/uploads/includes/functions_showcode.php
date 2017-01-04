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
class functions_showcode {

	function showantispam()
	{
		global $forums, $DB, $bboptions;

		if ($bboptions['useantispam']) {
			$regimagehash = md5( uniqid(microtime()) );
			mt_srand ((double) TIMENOW * 1000000);
			$imagestamp = mt_rand(1000,9999);
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."antispam
										(regimagehash, imagestamp, host, dateline)
									VALUES
										('".$regimagehash."', '".$imagestamp."', '".$DB->escape_string(SESSION_HOST)."', '".TIMENOW."')"
								);
			if ($bboptions['enableantispam'] == 'gd') {
				$show['gd'] = true;
				return array('imagehash' => $regimagehash, 'text' => 1);
			} else {
				$forums->func->rc = $regimagehash;
				return array('imagehash' => $regimagehash, 'text' => $forums->func->showimage());
			}
		}
	}

	function construct_extrabuttons()
	{
		global $forums, $DB, $_INPUT, $bboptions, $bbuserinfo;
		$forums->func->check_cache('bbcode');
		if ($bbuserinfo['canuseflash']) {
			$forums->cache['bbcode']['flash'] = array( 'bbcodetag' => 'flash', 'imagebutton' => 'images/editor/flash.gif' );
		}
		$arraynum = count($forums->cache['bbcode']);
		$i = 1;
		foreach ($forums->cache['bbcode'] AS $bbcode) {
			if ($bbcode['imagebutton']) {
				$bbcode['bbcodetag'] = strtolower($bbcode['bbcodetag']);
				$alt = sprintf( $forums->lang['_inserttags'], $bbcode['bbcodetag'] );
				if ($bbcode['twoparams'] == 1) {
					$extrabuttons[] = 'true';
					if($i<$arraynum){
						$buttonpush .= "'".$bbcode['bbcodetag']."',";
						$alts .= "'".$alt."',";
						$sty .= "'',";
						$i++;
					}else{
						$buttonpush .= "'".$bbcode['bbcodetag']."'";
						$alts .= "'".$alt."'";
						$sty .= "''";
					}
				} else {
					$extrabuttons[] = 'false';
					if($i<$arraynum){
						$buttonpush .= "'".$bbcode['bbcodetag']."',";
						$alts .= "'".$alt."',";
						$sty .= "'',";
						$i++;
					}else{
						$buttonpush .= "'".$bbcode['bbcodetag']."'";
						$alts .= "'".$alt."'";
						$sty .= "''";
					}
				}
			}
		}
		$extrabuttons = implode(",", (array) $extrabuttons);
		$extrabuttons = "[[".$buttonpush."],[".$alts."],[".$extrabuttons."],[".$sty."]]";
		return $extrabuttons;
	}
}

?>
