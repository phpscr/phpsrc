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
class invitereg 
{
	function get_has_authuser($condition = array())
	{
		global $DB;
		$searchconditon = implode (" AND ",$condition);
		$sql = "SELECT id FROM ".TABLE_PREFIX."user WHERE $searchconditon";
		$sqlresult = $DB->query($sql);
		$totaluser = $DB->num_rows($sqlresult);
		while ($row = $DB->fetch_array($sqlresult))
		{
			$userid[] = $row['id'];
		}
		return $userid;
	}

	function give_user_auth($condition = array())
	{
		global $DB,$bbuserinfo,$forums,$bboptions,$_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'invite' );
		if ($condition['userid'])
		{
			$sql = $DB->query("SELECT id,name FROM ".TABLE_PREFIX."user WHERE id IN (".implode(",",$condition['userid']).")");
			while ($user = $DB->fetch_array($sql))	
			{
				$username[$user['id']] = $user['name'];
			}
			$sum =0;
			$totaluser = count($condition['userid']);
			$invited = array();
			while($sum < $condition['invitetotalnum'] AND count($invited) < $totaluser)
			{
				$invitenum = mt_rand($condition['minnum'],$condition['maxnum']);
				$randid = mt_rand(0,($totaluser-1));
				while(array_key_exists($condition['userid'][$randid], $invited))
				{
					$randid = mt_rand(0,$totaluser-1);
				}
				$invited[$condition['userid'][$randid]] = $invitenum;
				$sum += $invitenum;
			}
			$gavensql = $DB->query("SELECT id,name,ishasinvite FROM ".TABLE_PREFIX."user WHERE ishasinvite<>''");
			if ($DB->num_rows($gavensql))
			{
				$gavens = array();
				while ($gaven = $DB->fetch_array($gavensql))
				{	
					$inviteinfo = unserialize($gaven['ishasinvite']);
					$gavens[$gaven['id']] = $inviteinfo['invitenum'];
				}
				$check = array_intersect(array_keys($gavens),array_keys($invited));
				if(is_array($check))
				{
					foreach ($check AS $checkuserid)
					{
						if($invited[$checkuserid]+$gavens[$checkuserid] > $condition['maxnum'])
						{
							$invited[$checkuserid] = $condition['maxnum'];
						}
						else
						{
							$invited[$checkuserid] = $invited[$checkuserid]+$gavens[$checkuserid];
						}
					}
				}

			}
			$sendcount = 0;
			$sendnum = 0;
			$sentuser = array();				
			foreach ($invited AS $sqluserid => $sqlnum)
			{
				$inviteuserinfo = serialize(array("invitenum" => $sqlnum,
												  "getinvitetime" => TIMENOW,
												  "expiry" =>$condition['expiry'],
											));
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite='".$inviteuserinfo."' WHERE id='".$sqluserid."'");
				$sentuser[] = $sqluserid;
				$sendcount++;
				$sendnum += $sqlnum;
			}			
			if (is_array($gavens))
			{
				$prenoinviteuser = array_diff($sentuser,array_keys($gavens));
			}
			else
			{
				$prenoinviteuser = $sentuser;
			}
			
			if($prenoinviteuser)
			{
				$_INPUT['title'] = $forums->lang['sendmessagetitle'];
				$_POST['post'] = $forums->lang['sendmessagebody'];
				$sendtouername = array();
				foreach ($prenoinviteuser AS $touserid)
				{
					$sendtouername[] = $username[$touserid];
				}
				$_INPUT['username'] = implode(';', $sendtouername);
				$_INPUT['noredirect'] = 1;
				require_once( ROOT_PATH.'includes/functions_private.php' );
				$pm = new functions_private();
				$pm->sendpm();
			}
			$sendinvitemessage = sprintf($forums->lang['invite_send'],$sendnum,count($invited),$sendcount); 
		}
		else
		{
			$sendinvitemessage = $forums->lang['send_noint'];
		}
		return $sendinvitemessage;
	}

	function send_invite($sendusername,$sendtype = "") 
	{
		global $DB,$_INPUT,$bbuserinfo,$forums,$bboptions;
		eval('$invitemailmsg = "'.$forums->lang['invitemailbody'].'";');
		if (!$sendtype)
		{
			$DB->query_unbuffered("INSERT INTO ".TABLE_PREFIX."inviteduser  ( userid , email , sendtime , expiry , regsterid , validatecode ) VALUES ('".$bbuserinfo['id']."','".addslashes($_INPUT['useremail'])."',".TIMENOW.",'".intval($bboptions['inviteduserexpiry'])."','0','".addslashes($_INPUT['invitecode'])."')");
		}
		else
		{
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."inviteduser SET sendtime=".TIMENOW.", validatecode='".addslashes($_INPUT['invitecode'])."', expiry='".intval($bboptions['inviteduserexpiry'])."' WHERE email='".addslashes($_INPUT['useremail'])."'");
		}
		require_once (ROOT_PATH . 'includes/functions_email.php');
		$send = new functions_email;
		if (!$_INPUT['useremailsubject'])
			$send->subject = $forums->lang['sendinvitesubject'];
		else
			$send->subject = $_INPUT['useremailsubject'];
		$send->to = $_INPUT['useremail'];
		if ($_INPUT['invitereason'])
			$invitemailmsg = $_INPUT['invitereason'];

		$send->build_message( $invitemailmsg );
		$info = $send->send_mail();		
		if (!$bbuserinfo['hasnolimitinv'] AND $sendtype != "resend") {
			$inviteuserinfo = unserialize($bbuserinfo['ishasinvite']);
			if (($inviteuserinfo['invitenum']-1) != 0) {
				$inviteuserinfo = serialize(array("invitenum" => $inviteuserinfo['invitenum']-1,
												  "getinvitetime" => $inviteuserinfo['getinvitetime'],
												  "expiry" =>$inviteuserinfo['expiry'],
											));
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite = '".$inviteuserinfo."' WHERE id=".$bbuserinfo['id']);
			} else {
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite = '' WHERE id=".$bbuserinfo['id']);
			}
		}
	}
}
?>