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
require ('./global.php');
class invite {

	function show()
	{
		global $forums, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditinvite']) {
			$forums->admin->print_cp_error($forums->lang['nopermissions']);
		}
		$forums->lang = $forums->func->load_lang($forums->lang, 'invite' );
		switch ($_INPUT['do'])
		{
			case 'list':
				$this->invitelist();
				break;
			case 'condition':
				$this->setcondition();
				break;
			case 'sendinvite':
				$this->sendinvite();
				break;
			case 'usersendinvitelist':
				$this->usersendinvitelist();
				break;
			case 'edituserinvite':
				$this->edituserinvite();
			case 'doedituserinvite':
				$this->doedituserinvite();
				break;
				break;
			case 'deluserinvite':
				$this->deluserinvite();
				break;
			case 'dosendinvite':
				$this->dosendinvite();
				break;
			case 'confirmsendinvite':		
 				$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=list",$forums->lang['regeditlist'],$forums->lang['tolist']);
				break;
			case 'editinvite':
				$this->editinvite();
				break;
			case 'doeditinvite':
				$this->doeditinvite();
				break;
			case 'delinvite':
				$this->delinvite();
				break;
			case 'dodeleteallinvite':
				$this->dodeleteallinvite();
				break;
			case 'dodeletedueinvite':
				$this->dodeletedueinvite();
				break;
			case 'dodeleteuserinvited':
				$this->dodeleteuserinvited();
				break;
			default:
				$this->invitelist();
				break;
		}
	}
	function invitelist()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle = $forums->lang['invitemanager'];
		$detail = $forums->lang['managehasinviteduser'];
		$show = $_INPUT['show'] ? intval($_INPUT['show']) : 10;
		$show = $show > 100 ? 100 : $show;
		$first = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$url = "show=".$show;
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=usersendinvitelist", $forums->lang['userinvitelist'] );
		$forums->admin->nav[] = array( "settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20", $forums->lang['inviteconset'] );
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=sendinvite", $forums->lang['sendinviteto'] );
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array(1 => array( 'do'  , 'dodeleteallinvite' ,
									    2 => array( 'url'   , $url ),
									  ));
		$forums->admin->print_form_header( $page_array,"selectallhas");	
		$forums->admin->print_table_start( $forums->lang['hasinviteuserlist'] );	
		$count = $DB->query_first( "SELECT count(*) as cnt FROM ".TABLE_PREFIX."user WHERE ishasinvite<>''");
		$links = $forums->func->build_pagelinks( array( 'totalpages'  => $count['cnt'],
											   'perpage'    => $show,
											   'curpage'  => $first,
											   'pagelink'    => "inviteset.php?{$forums->sessionurl}do=list&amp;$url",
									  )      );
		$invitelistsql = $DB->query("SELECT id,name,ishasinvite FROM ".TABLE_PREFIX."user WHERE ishasinvite<>'' LIMIT $first, $show");
		if ($DB->num_rows($invitelistsql))
		{
			$forums->admin->print_cells_row(array($forums->lang['username'],$forums->lang['hasinvitenum'],$forums->lang['invitesendtime'],$forums->lang['inviteuseexpiry'],$forums->lang['editandelinvite'],"<input type='checkbox' name='allbox' onclick=\"CheckAll(document.selectallhas)\" />".$forums->lang['selectdelete']));

			while ($row = $DB->fetch_array($invitelistsql))
			{
				$inviteinfo = unserialize($row['ishasinvite']);
				if ($inviteinfo)
				{
					$manage = "<a href = ?{$forums->sessionurl}&amp;do=editinvite&amp;userid=".$row[id].">".$forums->lang['edit']."</a>&nbsp;&nbsp;<a href=inviteset.php?{$forums->sessionurl}&amp;do=delinvite&amp;userid=".$row[id].">".$forums->lang['quickdelete']."</a>";
					if ($inviteinfo['invitenum'] AND $inviteinfo['getinvitetime']+3600*24*$inviteinfo['expiry'] > TIMENOW)
					{
						$tdarray = array($row['name'],$inviteinfo['invitenum'],date("Y-m-d H:i:s",$inviteinfo['getinvitetime']),date("Y-m-d H:i:s",$inviteinfo['getinvitetime']+3600*24*$inviteinfo['expiry']),$manage,$forums->admin->print_checkbox_row("invite[".$row['id']."]",'',$row['id']));
						$forums->admin->print_cells_row($tdarray);
					}
					else
					{
						$overdueexpiryuser[] = $row['id'];
						$duetdarrays[] = array($row['name']);
					}
				}
			}
			$forums->admin->print_form_submit($forums->lang['confirmdelete']);
			$forums->admin->print_cells_single_row( $links, "right", "tdrow2");
		}
		else
		{
			$forums->admin->print_cells_single_row( $forums->lang['nouserforinvite'], "center");
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_hidden_row(array(1=>array("pp", $first)));
		$forums->admin->print_hidden_row(array(1=>array("show", $show)));
		$forums->admin->print_form_end();
		if (is_array($overdueexpiryuser))
		{
			$forums->admin->print_table_start( $forums->lang['overdueexpiryuser'] );
			$overdueusers = implode(",", $overdueexpiryuser);
			if ($_INPUT['continue'])			
			{
				echo "<meta http-equiv='refresh' content=\"1; url=inviteset.php?".$forums->sessionurl."do=dodeletedueinvite&amp;pp=".$_INPUT['pp']."&amp;invitedues=$overdueusers&amp;show=".$_INPUT['show']."\">\n";
			}
			else
			{
				echo "<input type='button' class='button' value='".$forums->lang['clearuser']."' onclick='self.location.href=\"?".$forums->sessionurl."do=dodeletedueinvite&amp;pp=$first&amp;showinfo=1&amp;show=$show&amp;invitedues=$overdueusers\"' title='$title' />";
			}
			if($_INPUT['showinfo'])
			{
				echo $forums->lang['clearuserload'];
			}
			$forums->admin->print_table_footer();
			$forums->admin->print_hidden_row(array(1=>array("pp", $first)));
			$forums->admin->print_hidden_row(array(1=>array("show", $show)));
			$forums->admin->print_hidden_row(array(1=>array("invitedues", $overdueusers)));
		}
		$forums->admin->print_cp_footer();
	}

	function dodeleteallinvite()
	{
		global $DB,$_INPUT,$forums;
		if(is_array($_INPUT['invite']))
		{
			$invite = implode(",",$_INPUT['invite']);
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite='' WHERE id IN (".$invite.")"); 
			$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=list&amp;pp=".$_INPUT['pp']."&amp;show=".$_INPUT['show'],$forums->lang['userinvitedlist'],$forums->lang['delinvitesuc']);
		}
		else
		{
			$forums->admin->print_cp_error($forums->lang['noselectuser']);
		}
	}

	function dodeletedueinvite()
	{
		global $DB,$_INPUT,$forums;
		if($_INPUT['invitedues'])
		{
			$invite = $_INPUT['invitedues'];
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite='' WHERE id IN (".$invite.")"); 		$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=list&amp;pp=".$_INPUT['pp']."&amp;showinfo=1&amp;continue=1&amp;show=".$_INPUT['show'], $forums->lang['userinvitedlist'], $forums->lang['delinvitesuc']);
		}
		else
		{
			$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=list&amp;pp=".$_INPUT['pp']."&amp;show=".$_INPUT['show'], $forums->lang['userinvitedlist'], $forums->lang['noselectuser']);
		}
	}

	function sendinvite()
	{
		global $forums, $DB, $_INPUT,$bboptions,$bbgroupname;
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=usersendinvitelist", $forums->lang['userinvitelist'] );
		$forums->admin->nav[] = array( "settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20", $forums->lang['inviteconset'] );
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=sendinvite", $forums->lang['sendinviteto'] );
		if ($bboptions['allowregistration'])
		{
			$isallowregistration = sprintf($forums->lang['isallowregistration'],"settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=15");
 			$forums->admin->print_cp_error($isallowregistration);
		}
		if (!$bboptions['isopeninvite'])
		{
			$inviteisnotopen = sprintf($forums->lang['inviteisnotopen'],"settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20");
 			$forums->admin->print_cp_error($inviteisnotopen);
		}
		require_once ( ROOT_PATH."includes/functions_invite.php");
		$invitereg = new invitereg();
		if ($bboptions['limitinvitegroup'])
		{
			$limitgroupid = $bboptions['limitinvitegroup']; 
			$limitcon[] = "usergroupid IN (".$limitgroupid.")"; 
		}		
		if ($bboptions['nolimitinviteusergroup'])
		{
			$limitcon[] = "usergroupid NOT IN (".$bboptions['nolimitinviteusergroup'].")";
		}
		if ($bboptions['limitposttitlenum'])
		{
			$limitcon[] = "posts >= ".$bboptions['limitposttitlenum'];
		}			
		if ($bboptions['limitregtime'])
		{
			$limitcon[] = TIMENOW."-joindate >= 3600*24*".$bboptions['limitregtime'];
		}	
		if ($bboptions['timenotlogin'])
		{
			$limitcon[] = TIMENOW."-lastactivity < (".intval($bboptions['timenotlogin']*3600*24).")";
		}

		$hasauthuser = $invitereg->get_has_authuser($limitcon);
		if (!$hasauthuser)
		{
			$hasnouseraccord = sprintf($forums->lang['hasnouseraccord'],"settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20");
			$forums->admin->print_cp_error($hasnouseraccord);	
		}
		$pagetitle = $forums->lang['sendinviteto'];
		$detail = $forums->lang['sendinvitetouser'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$DB->query( "SELECT usergroupid, grouptitle FROM ".TABLE_PREFIX."usergroup" );
		while( $row = $DB->fetch_array() ) 
		{
			$groupname[$row['usergroupid']] = $row['grouptitle'];
		}
		$page_array = array( 1 => array( 'do'  , 'dosendinvite'  )
						   );
		$forums->admin->print_form_header( $page_array );
		$forums->admin->print_table_start( $forums->lang['haslimitinvitecon'] ); 
		$groupid = explode(",",$bboptions['limitinvitegroup']);
		foreach($groupid AS $id)
		{
			$playlimit .= "<div>$groupname[$id]</div>";
		}
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['haslimitinvitegroup']."</strong>", $playlimit) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['haslimitinvitepost']."</strong>", $bboptions['limitposttitlenum']."&nbsp;") );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['haslimitinvitetime']."</strong>", $bboptions['limitregtime'].$forums->lang['days']) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['haslimitinvitetimenotlogin']."</strong>", $bboptions['timenotlogin'].$forums->lang['days']) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['haslimitinvitetotalnum']."</strong>",$forums->admin->print_input_row("invitetotalnum",100) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['messagetitle']."</strong>".$forums->lang['messagetitlexplain']."",$forums->admin->print_input_row("invitemsgtitle",$forums->lang['sendmessagetitle'],'','','50') ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['messagebody']." </strong>",$forums->admin->print_textarea_row("invitemsgbody",$forums->lang['sendmessagebody'],'20') ) );
		$forums->admin->print_cells_row( array( array ($forums->lang['inviteexplain1'].count($hasauthuser).$forums->lang['inviteexplain2'],2)));
		$hasauthuser = implode(",",$hasauthuser);
		$forums->admin->print_form_submit($forums->lang['sendinvite']);
		$forums->admin->print_table_footer();
		$forums->admin->print_hidden_row(array(1=>array("hasauthuserid",$hasauthuser)));
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}
	function dosendinvite()
	{
		global $forums, $DB, $_INPUT,$bboptions;
		require_once ( ROOT_PATH."includes/functions_invite.php");
		$invitereg = new invitereg();
		$hasauthuser = explode(",",$_INPUT['hasauthuserid']);
		$limitinvitemessage = $invitereg->give_user_auth(array("userid"=>$hasauthuser,
															   "invitetotalnum"=>$_INPUT['invitetotalnum'],
															   "expiry"=>$bboptions['invitexpiry'],
															   "minnum"=>$bboptions['inviteminnum'],
															   "maxnum"=>$bboptions['invitemaxnum']
														));

		$pagetitle = $forums->lang['invitesendresult'];
		$detail = $forums->lang['displayresult'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array( 1 => array( 'do'  , 'confirmsendinvite'  )
						   );
		$forums->admin->print_form_header( $page_array );
		$forums->admin->print_table_start( $forums->lang['limitdisplayresult'] );
		$forums->admin->print_cells_row( array( $limitinvitemessage ) );
		$forums->admin->print_form_submit($forums->lang['ok']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}
	function editinvite()
	{
		global $DB,$forums,$_INPUT;
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=usersendinvitelist", $forums->lang['userinvitelist'] );
		$forums->admin->nav[] = array( "settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20", $forums->lang['inviteconset'] );
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=sendinvite", $forums->lang['sendinviteto'] );
		$pagetitle = $forums->lang['editinvite'];
		$detail = $forums->lang['editinviteuser'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array( 1 => array( 'do'  , 'doeditinvite'  )
						   );
		$forums->admin->print_form_header( $page_array );
		$forums->admin->print_table_start( $forums->lang['editinvite'] );
		$getinviteinfo = $DB->query_first("SELECT name,ishasinvite FROM ".TABLE_PREFIX."user WHERE id='".$_INPUT['userid']."'");
		$getuserinviteinfo = unserialize($getinviteinfo['ishasinvite']);
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['username']."</strong>",$getinviteinfo['name'] ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['usergetinvitetime']."</strong>", date("Y-m-d H:i:s",$getuserinviteinfo[getinvitetime] )) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['userhasinvitenum']."</strong>",$forums->admin->print_input_row("userlimitnum",$getuserinviteinfo['invitenum']) ) );
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['inviteuseexpiry']."</strong><br />".$forums->lang['daysunit'],$forums->admin->print_input_row("userexpiry",$getuserinviteinfo['expiry']).$forums->lang['days']) );
		$forums->admin->print_form_submit($forums->lang['entermod']);
		$forums->admin->print_table_footer();
		$forums->admin->print_hidden_row(array(1=>array('userid',$_INPUT['userid'])));
		$forums->admin->print_hidden_row(array(1=>array('getinvitetime',$getuserinviteinfo['getinvitetime'])));
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();

	}
	function doeditinvite()
	{
		global $DB,$forums,$_INPUT;
		$inviteuserinfo = serialize(array("invitenum" => $_INPUT['userlimitnum'],
										  "getinvitetime" => $_INPUT['getinvitetime'],
										  "expiry" =>$_INPUT['userexpiry'],
									));
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite='".$inviteuserinfo."' WHERE id = '".intval($_INPUT['userid'])."'");
 		$forums->admin->redirect("inviteset.php?do=list", $forums->lang['userinvitedlist'], $forums->lang['updateinvite']);

	}

	function delinvite()
	{
		global $DB,$_INPUT,$forums;
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET ishasinvite='' WHERE id = '".intval($_INPUT['userid'])."'"); 		$forums->admin->redirect("inviteset.php?do=list", $forums->lang['userinvitedlist'], $forums->lang['delinvitesuc']);
	}

	function usersendinvitelist()
	{
		global $forums, $DB, $_INPUT,$bbuserinfo,$bboptions;
		$show = $_INPUT['show'] ? intval($_INPUT['show']) : 10;
		$show = $show > 100 ? 100 : $show;
		$first = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$url = "show=".$show;
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=usersendinvitelist", $forums->lang['userinvitelist'] );
		$forums->admin->nav[] = array( "settings.php?".$forums->sessionurl."do=setting_view&amp;groupid=20", $forums->lang['inviteconset'] );
		$forums->admin->nav[] = array( "inviteset.php?".$forums->sessionurl."do=sendinvite", $forums->lang['sendinviteto'] );
		$pagetitle = $forums->lang['invitemanager'];
		$detail = $forums->lang['managehasinviteduser'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array(1 => array( 'do'  , 'dodeleteuserinvited' ,
									    4 => array( 'url'   , $url      ),
									    ));	
		$forums->admin->print_form_header( $page_array,"selectallinvited");
		$forums->admin->print_table_start( $forums->lang['userinviteyet'] );
		$count = $DB->query_first( "SELECT count(*) as cnt FROM ".TABLE_PREFIX."inviteduser");
		$links = $forums->func->build_pagelinks( array( 'totalpages'  => $count['cnt'],
											   'perpage'    => $show,
											   'curpage'  => $first,
											   'pagelink'    => "inviteset.php?{$forums->sessionurl}do=usersendinvitelist&amp;$url",
									  )      );	
		$invitelistsql = $DB->query("SELECT a.*,b.name,b.ishasinvite,b.usergroupid FROM ".TABLE_PREFIX."inviteduser a LEFT JOIN ".TABLE_PREFIX."user b ON a.userid=b.id LIMIT $first, $show");
		if ($DB->num_rows($invitelistsql))
		{
			$forums->admin->print_cells_row(array($forums->lang['sendinviteuser'],$forums->lang['inviteduseremail'],$forums->lang['invitesenddate'],$forums->lang['inviteuserremainnum'],$forums->lang['inviteduserisreg'],$forums->lang['editandelinvite'],"<input type='checkbox' name='allbox' onclick=\"CheckAll(document.selectallinvited)\" />".$forums->lang['selectdelete']));
			$nolimitinv = explode(",",$bboptions['nolimitinviteusergroup']);
			while ($row = $DB->fetch_array($invitelistsql))
			{
				if ($row['regsterid'])
				{
					$isreg = $forums->lang['isreged'];
				}
				else
				{
					$isreg = $forums->lang['isnotreged'];
				}
				if(is_array($nolimitinv))
				{		
					if(in_array($row['usergroupid'], $nolimitinv))
					{		
						$isnolimituse = $forums->lang['hasnolimitinvite'];
					}
					else
					{
						$userremainnum = unserialize($row['ishasinvite']);
						$isnolimituse = $userremainnum['invitenum']?$userremainnum['invitenum']:0;
					}
				}
				else
				{
					$userremainnum = unserialize($invitelistsql['ishasinvite']);
					$isnolimituse = $userremainnum['invitenum'];
				}
				$manage = "<a href = ?{$forums->sessionurl}do=edituserinvite&amp;invitedid=".$row['invitedid'].">".$forums->lang['edit']."</a>&nbsp;&nbsp;<a href = ?{$forums->sessionurl}do=deluserinvite&amp;invitedid=".$row['invitedid'].">".$forums->lang['quickdelete']."</a>";
				$tdarray = array($row['name'],$row['email'],date("Y-m-d H:i:s",$row['sendtime']),$isnolimituse,$isreg,$manage,$forums->admin->print_checkbox_row("invited[".$row['invitedid']."]",'',$row['invitedid']));
				$forums->admin->print_cells_row($tdarray);
			}
			$forums->admin->print_cells_single_row( $links, "right", "tdrow2");
			$forums->admin->print_form_submit($forums->lang['confirmdelete']);
		}
		else
		{
			$forums->admin->print_cells_single_row( $forums->lang['nouserinvited'], "center" );
		}		
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}
	
	function dodeleteuserinvited()
	{
		global $DB,$_INPUT,$forums;
		if(is_array($_INPUT[invited]))
		{
			$invited = implode(",",$_INPUT['invited']);
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."inviteduser WHERE invitedid IN (".$invited.")");
 			$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=usersendinvitelist",$forums->lang['userinvitelist'],$forums->lang['inviteduserdel']);
		}
		else
		{
 			$forums->admin->redirect("inviteset.php?".$forums->sessionurl."do=usersendinvitelist",$forums->lang['userinvitelist'],$forums->lang['noselectuser']);
		}
	}

	function edituserinvite()
	{
		global $DB,$forums,$_INPUT;
		$forums->admin->nav[] = array( "inviteset.php".$forums->sessionurl."do=usersendinvitelist", $forums->lang['userinvitelist'] );
		$forums->admin->nav[] = array( "settings.php".$forums->sessionurl."do=setting_view&amp;groupid=20", $forums->lang['inviteconset'] );
		$forums->admin->nav[] = array( "inviteset.php".$forums->sessionurl."do=sendinvite", $forums->lang['sendinviteto'] );
		$pagetitle = $forums->lang['editinvite'];
		$detail = $forums->lang['editinviteuser'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		$page_array = array( 1 => array( 'do'  , 'doedituserinvite'  )
						   );
		$forums->admin->print_form_header( $page_array );
		$forums->admin->print_table_start( $forums->lang['editinvite'] );
		$getinviteinfo = $DB->query_first("SELECT a.*,b.name FROM ".TABLE_PREFIX."inviteduser a LEFT JOIN ".TABLE_PREFIX."user b ON a.userid=b.id WHERE a.invitedid='".$_INPUT['invitedid']."'");
		$forums->admin->print_cells_row( array( "<strong>".$forums->lang['inviteduserexpiry']."</strong><br />".$forums->lang['daysunit'],$forums->admin->print_input_row("inviteduserexpiry",$getinviteinfo[expiry]).$forums->lang['days']) );
		$forums->admin->print_form_submit($forums->lang['entermod']);
		$forums->admin->print_table_footer();
		$forums->admin->print_hidden_row(array(1=>array('invitedid',$_INPUT['invitedid'])));
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function doedituserinvite()
	{
		global $DB,$forums,$_INPUT;
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."inviteduser SET expiry='$_INPUT[inviteduserexpiry]' WHERE invitedid = '$_INPUT[invitedid]'");
 		$forums->admin->redirect("inviteset.php?do=usersendinvitelist", $forums->lang['userinvitelist'], $forums->lang['inviteduserupdate']);
	}

	function deluserinvite()
	{
		global $DB,$_INPUT,$forums;
		$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."inviteduser WHERE invitedid ='".$_INPUT['invitedid']."'");
 		$forums->admin->redirect("inviteset.php?do=usersendinvitelist", $forums->lang['userinvitelist'], $forums->lang['inviteduserdel']);
	}
}
$out = new invite;
$out -> show();
?>