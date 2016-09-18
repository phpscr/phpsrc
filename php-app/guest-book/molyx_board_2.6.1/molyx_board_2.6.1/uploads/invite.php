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
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'invite');

require_once('./global.php');

class newinvite
{
	var $folderid='';
	var $canupload = 0;
	var $user = array();
	var $pmselect = '';
	var $userid = 0;
	var $getpmid = 0;
	var $message= '';

    function show()
    {
    	global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'usercp' );
		$forums->lang = $forums->func->load_lang($forums->lang, 'private' );
		if ( ! $bbuserinfo['id'] ) {
			$forums->func->standard_error("notlogin");
		}
    	switch($_INPUT['do']) {
			case 'invite':
    			$this->writeinvite();
    			break;
			case 'sendinvite':
    			$this->sendinvite();
    			break;
    		default:
    			$this->writeinvite();
    			break;
    	}
 	}
 	function writeinvite($error = "")
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$pagetitle = $forums->lang['reasontitle'];
		$bbuserinfo['folder_links'] = 0;
		$bbuserinfo['pmfolders'] = unserialize($bbuserinfo['pmfolders']);
		if (count($bbuserinfo['pmfolders']) < 2) {
			$bbuserinfo['pmfolders'] = array(-1 => array( 'pmcount' => 0, 'foldername' => $forums->lang['_outbox'] ), 0 => array( 'pmcount' => 0, 'foldername' => $forums->lang['_inbox'] ) );
		}
		if($bboptions['allowregistration'] OR !$bboptions['isopeninvite']) {
			$forums->func->standard_error('notopeninvite');
		}

		if (!$bbuserinfo['hasnolimitinv'] AND !$bbuserinfo['haslimitinv']) {
			$forums->func->standard_error('noinvite');
		}
		if ($bbuserinfo['haslimitinv'] AND !$bbuserinfo['haslimitinv']['invitenum']) {
			$forums->func->standard_error('noinvite');
		}
		if(!$bbuserinfo['hasnolimitinv'] AND $bbuserinfo['haslimitinv']['invitetimend'] < time()  ) {
			$DB->query ("UPDATE ".TABLE_PREFIX."user SET ishasinvite = '' WHERE id=".$bbuserinfo[id]);
			$forums->func->standard_error('invexpiry');
		} else {
			$count = $DB->query_first("SELECT count(*) AS total FROM ".TABLE_PREFIX."inviteduser WHERE userid = ".$bbuserinfo['id']."");
			$first = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
			$maxresults = $_INPUT['max_results'] ? $_INPUT['max_results'] : 3;
			if ($count['total'] > 0) {
				$isinviteduser = true;
				$invitepagenav = $forums->func->build_pagelinks(  array( 'totalpages'  => $count['total'],
													'perpage'    => $maxresults,
													'curpage'  => $first,
													'pagelink'     => "invite.php?{$forums->sessionurl}&amp;max_results={$maxresults}"
												  )
										   );
				$userinviteinfosql = $DB->query("SELECT inu.*,u.name,u.id FROM ".TABLE_PREFIX."inviteduser inu LEFT JOIN ".TABLE_PREFIX."user u ON  inu.regsterid=u.id WHERE inu.userid = ".$bbuserinfo['id']." LIMIT {$first}, {$maxresults}");
				$userinviteinfo = array();
				while ($row = $DB->fetch_array($userinviteinfosql)) {
					$time_r = TIMENOW - $row['sendtime'];
					$time_e = $row['expiry'] * 60 * 60 * 24 ;
					$userinviteinfo[] = $row;
					if ( $time_r <= $time_e AND !$row['regsterid']) {
						$thisinvitelink[$row['invitedid']] = array("link"=>"invite.php?".$forums->sessionurl."send=resend&amp;inviteid=".$row['invitedid'],"lang"=>$forums->lang['invited_resend']);
					} else if($time_r > $time_e) {
						$thisinvitelink[$row['invitedid']] = array("link"=>"invite.php?".$forums->sessionurl."send=sendagain&amp;inviteid=".$row['invitedid'],"lang"=>$forums->lang['invited_sendagain']);
					}
				}
			}
			define("STR","0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
			for($i=0;$i<10;$i++) {
				$invitecode .= substr(STR,rand(0,61),1);
			}
			$_INPUT['inviteid'] = intval($_INPUT['inviteid']);
			if ($_INPUT['send'] AND $_INPUT['inviteid']) {
				$alreadysendinvite = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."inviteduser WHERE invitedid = ".$_INPUT['inviteid']);
				$emailinputreadonly = "readonly";
				$_POST['useremail'] = $alreadysendinvite['email'];
			}
			$alerterror = $error?$error:"";
			if(!$bbuserinfo['hasnolimitinv']) {
				$bbuserinfo['haslimitinv']['invitetimend'] = $forums->func->get_time($bbuserinfo['haslimitinv']['invitetimend'], "Y-m-d H:i:s");
			}
			$limitexpiry = $forums->func->get_time($bboptions['inviteduserexpiry'], "Y-m-d H:i:s");
			eval('$invitemailsub = "'.$forums->lang['sendinvitesubject'].'";');
			eval('$invitemailmsg = "'.$forums->lang['invitemailbody'].'";');
			include $forums->func->load_template('usercp_invitesend');
			exit;
		}
 	}
 	function sendinvite()
 	{
 		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'invite' );
		require_once(ROOT_PATH."includes/functions_invite.php");
		$invitereg = new invitereg();
		$email = $forums->func->clean_email($_INPUT[useremail]);
		if ( ! $email ) {
    		$this->writeinvite($forums->lang['inputerror']);
			exit;
		}
		$in_email  = trim($forums->func->strtolower($_INPUT['useremail']));
		if ($email_check = $DB->query_first( "SELECT id FROM ".TABLE_PREFIX."user WHERE email='".$in_email."'" ) ) {
			$forums->func->standard_error('invisregister');
		}
		$hasinvitednum = $bbuserinfo['haslimitinv']['invitenum'];
		if ($_INPUT['send'] != "resend" AND $_INPUT['send'] != "sendagain") {
			if ($email_check = $DB->query_first( "SELECT email FROM ".TABLE_PREFIX."inviteduser WHERE email='".$in_email."'" ) ) {
				$forums->func->standard_error('invisinvited');
			}
		} else if($_INPUT['send'] != "resend") {
			$hasinvitednum = $hasinvitednum-1;
		}
		$info = $invitereg->send_invite($bbuserinfo['name'],$_INPUT['send']);
		if (!($hasinvitednum) AND !$bbuserinfo['hasnolimitinv']){
			$forums->func->redirect_screen($forums->lang['iniend'],"index.php?".$forums->sessionurl);
		} else {
			$forums->func->redirect_screen($forums->lang['successtosend'],"invite.php?".$forums->sessionurl."do=invite");
		}
 	}
}

$output = new newinvite();
$output->show();

?>