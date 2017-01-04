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
define('THIS_SCRIPT', 'bank');

require_once('./global.php');

class bank
{
	var $banksettings = array();

	function show()
	{
		global $forums, $_INPUT, $bbuserinfo;
		if ( !$bbuserinfo['id'] ) {
			$forums->func->standard_error("notlogin");
		}
		$forums->lang = $forums->func->load_lang($forums->lang, 'bank' );
		$forums->func->check_cache('banksettings');
		$this->banksettings = $forums->cache['banksettings'];
		if (!$this->banksettings['openbank']) {
			$forums->func->standard_error("bankclosed");
		}
		require_once(ROOT_PATH.'includes/xfunctions_bank.php');
		$this->bankfunc = new bankfunc();
		switch ($_INPUT['do'])
		{
			case 'purge':
				$this->purge();
				break;
			case 'dopurge':
				$this->dopurge();
				break;
			case 'mkaccount':
				$this->mkaccount();
				break;
			case 'showlog':
				$this->showlog();
				break;
			case 'takeloan':
				$this->takeloan();
				break;
			case 'pbclean':
				$this->pbclean();
				break;
			case 'returnloan':
				$this->returnloan();
				break;
			case 'transfer':
				$this->transfer();
				break;
			case 'depositorwithdraw':
				$this->depositorwithdraw();
				break;
			case 'borsreputation':
				$this->borsreputation();
				break;
			default:
				$this->showmain();
				break;
        }
	}

	function calculate_credit()
	{
		global $forums, $bbuserinfo, $DB;
		$banksettings = $this->banksettings;

		// ======================================
		// 评估值和信誉额计算方法
		// ======================================
		// 基础信誉额
		// 积分 = 基础*(100+积分)/250
		// 发贴数量 = 发贴数量*3/4
		// 精华 = 基础*精华*5/发贴数 => 计算比例
		// 注册时间(小时)
		// 固定资产 = 固定资产*130/100
		// 总和 = 所有相加*(发贴数+精华*3)/发贴数
		// 按照每 +-1 积分 +-2% 信誉额总和
		// ======================================
		$creditinfo = array();
		$basic = !$banksettings['bankloanusegroup'] ? $banksettings['bankloanamount'] : $bbuserinfo['bankloanlimit'];
		$creditinfo['basic'] = array (	"name"		=> $forums->lang['basiccredit'],
							"desc"		=> $forums->lang['basiccreditdesc'],
							"factor"	=> $basic,
							"value"		=> $basic );
		$reputation = $basic*(100+$bbuserinfo['reputation']) / 250;
		$creditinfo['reputation'] = array (	"name"		=> $forums->lang['reputation'],
														"desc"		=> $forums->lang['reputationdesc'],
														"factor"	=> $bbuserinfo['reputation'],
														"value"		=> $reputation );
		$posts = $bbuserinfo['posts'] * 3 / 4;
		$creditinfo['posts'] =		array (	"name"		=> $forums->lang['posts'],
							"desc"		=> $forums->lang['postsdesc'],
							"factor"	=> $bbuserinfo['posts'],
							"value"		=> $posts );
		$quint = $bbuserinfo['posts'] ? $basic*$bbuserinfo['quintessence']*5/$bbuserinfo['posts'] : 0;
		$creditinfo['quint'] =		array (	"name"		=> $forums->lang['quintessence'],
							"desc"		=> $forums->lang['quintessencedesc'],
							"factor"	=> $bbuserinfo['quintessence'],
							"value"		=> $quint );
		$jointime = intval((TIMENOW - $bbuserinfo['joindate']) / 3600);
		$creditinfo['jointime'] = 	array (	"name"		=> $forums->lang['jointime'],
							"desc"		=> $forums->lang['jointimedesc'],
							"factor"	=> $jointime,
							"value"		=> $jointime );
		//$tmpdata = $DB->query_first("SELECT SUM(itemvalue) as totalvalue FROM ".TABLE_PREFIX."userinventory WHERE itemvalue > 0 AND ownerid = ".$bbuserinfo['id']);
		//$realestate = $tmpdata['totalvalue']!=NULL?$tmpdata['totalvalue']:0;
		$realestate = 0;
		$reval = $realestate*13/10;
		$realestate = round($realestate, 2);
		$realestate = number_format($realestate, 2, '.', '');
		$creditinfo['realestate'] = 	array (	"name"		=> $forums->lang['realestate'],
							"desc"		=> $forums->lang['realestatedesc'],
							"factor"	=> $realestate,
							"value"		=> $reval );

		$creditscore = $basic + $jointime + $posts + $reputation + $quint + $reval;
		// 计算精华帖比例
		if ( $bbuserinfo['posts'] > 1 && $bbuserinfo['quintessence'] > 1 )
			$creditscore = $creditscore*($bbuserinfo['posts']+$bbuserinfo['quintessence']*3)/$bbuserinfo['posts'];
		// 按照每 +-1 积分 +-2% 信誉额
		$creditscore = $creditscore*(50+$bbuserinfo['reputation'])/50;

		$creditinfo['creditamount'] = $creditscore;
		$creditinfo['creditscore'] = $forums->func->fetch_number_format($creditscore);
		foreach ( $creditinfo AS $key => $val)
		{
			if ( $key != "creditscore" && $key != "creditamount" )
			{
				$val['value'] = round($val['value'], 2);
				$val['value'] = number_format($val['value'], 2, '.', '');
			}
			$creditinfo[$key] = $val;
		}
		if ( $banksettings['bankloanreglimit'] && $creditinfo['jointime']['factor'] < $banksettings['bankloanreglimit'] )
			$creditinfo['jointime']['factor'] = "<span class='loanwarn'>".$creditinfo['jointime']['factor']."</span>";
		if ( $banksettings['bankloanpostlimit'] && $creditinfo['posts']['factor'] < $banksettings['bankloanpostlimit'] )
			$creditinfo['posts']['factor'] = "<span class='loanwarn'>".$creditinfo['posts']['factor']."</span>";
		if ( $banksettings['bankloanreplimit'] && $creditinfo['reputation']['factor'] < $banksettings['bankloanreplimit'] )
			$creditinfo['reputation']['factor'] = "<span class='loanwarn'>".$creditinfo['reputation']['factor']."</span>";
		return $creditinfo;
	}

	function mkloantypelist()
	{
		$banksettings = $this->banksettings;
		$listcode = array();
		$types = $banksettings['bankloantimelimit'];
		$ints = $banksettings['bankloaninterest'];
		if ( !strstr($types, ",") && !strstr($ints, ",") && intval($stypes) && intval($ints) )
		{
			$listcode[] = array( "type"	=> $types, "interest"	=> $ints );
			return $listcode;
		}
		if ( strstr($types, ",") )
			$types = explode(",", $types);
		if ( strstr($ints, ",") )
			$ints = explode(",", $ints);
		if ( is_array($types) && is_array($ints) && count($types) == count($ints) )
		{
			for ($i = 0, $n = count($types); $i < $n; $i++)
			{
				$types[$i] = trim($types[$i]);
				$itns[$i] = trim($ints[$i]);
				if (!intval($types[$i]) || !intval($ints[$i])) {
					continue;
				}
				$listcode[] = array('type' => $types[$i], 'interest' => $ints[$i]);
			}
		}
		else if ( is_array($types) && is_array($ints) && count($types) != count($ints) )
			$listcode[] = array( "type"	=> $types[0],
					     "interest"	=> $ints[0] );
		else if ( is_array($types) && !is_array($ints) && intval($ints) )
			$listcode[] = array( "type"	=> $types[0],
					     "interest"	=> $ints );
		else if ( !is_array($types) && is_array($ints) && intval($types) )
			$listcode[] = array( "type"	=> $types,
					     "interest"	=> $ints[0] );

		return $listcode;
	}

	function fetch_userinfo($crediton=0)
	{
		global $forums, $bbuserinfo;
		$banksettings = $this->banksettings;
		$thisuserinfo = $forums->func->fetch_user( $bbuserinfo );
		if($thisuserinfo['mkaccount']>0) {
			$thisuserinfo['mkaccount'] = $forums->func->get_date( $thisuserinfo['mkaccount'], 1 );
		}
		else {
			$thisuserinfo['mkaccount'] = $forums->lang['notcreateaccount'];
		}
 		if ( $banksettings['bankloanonoff'] && $crediton ) {
 			$thisuserinfo['credit'] = $this->calculate_credit();
		}
		return $thisuserinfo;
	}

	function generate_toprich()
	{
		global $forums, $DB;
		$toprichdata = $DB->query("SELECT id, name, (bank + cash) as money FROM ".TABLE_PREFIX."user WHERE mkaccount > 0 ORDER BY money DESC LIMIT 0, 10");
		$toprich = array();
		$topnum = 0;
		if ($DB->num_rows($toprichdata)) {
			while ( $a = $DB->fetch_array($toprichdata) ) {
				$topnum++;
				$a['topnum'] = $topnum;
				$a['money'] = $forums->func->fetch_number_format($a['money']);
				$toprich[] = $a;
			}
		}
		return $toprich;
	}

	function can_loan()
	{
		global $bbuserinfo;
		$banksettings = $this->banksettings;
		if ( !$banksettings['bankloanonoff'] ) {
			return FALSE;
		}
		if ( $banksettings['bankloanusegroup'] && !$bbuserinfo['bankloanlimit'] ) {
			return FALSE;
		} elseif ( !$banksettings['bankloanamount'] ) {
			return FALSE;
		}
		if ( !$bbuserinfo['id'] || $bbuserinfo['usergroupid'] == 1 || $bbuserinfo['usergroupid'] == 5 ) {
			return FALSE;
		}
		if ( !$bbuserinfo['mkaccount'] || $bbuserinfo['mkaccount'] == -1 ) {
			return FALSE;
		}
		if ( $banksettings['bankloanreglimit'] && ((TIMENOW-$bbuserinfo['joindate'])/3600) < $banksettings['bankloanreglimit'] ) {
			return FALSE;
		}
		if ( $banksettings['bankloanpostlimit'] && $bbuserinfo['posts'] < $banksettings['bankloanpostlimit'] ) {
			return FALSE;
		}
		if ( $banksettings['bankloanreplimit'] && $bbuserinfo['reputation'] < $banksettings['bankloanreplimit'] ) {
			return FALSE;
		}
		return TRUE;
	}

	function showmain()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$banksettings = $this->banksettings;
		$pagetitle = $forums->lang['bank'].' -> '.$bboptions['bbtitle'];
 		$nav = array( "<a href='bank.php?{$forums->sessionurl}'>" . $forums->lang['bank']."</a>" );
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		$thisuserinfo = $this->fetch_userinfo(1);
		$toprich = $this->generate_toprich();
		if (!$bbuserinfo['mkaccount']) {
			$forums->lang['createaccountinfo'] = sprintf( $forums->lang['createaccountinfo'], $bboptions['bbtitle'], $banksettings['bankcost'], $banksettings['bankcurrency'], $banksettings['bankinterest'], $banksettings['bankexcost'], $banksettings['bankexcostskip'], $banksettings['bankcurrency'] );
		} else if ($bbuserinfo['mkaccount'] == '-1') {
			$forums->lang['bankrupt'] = sprintf( $forums->lang['bankrupt'], $bboptions['bbtitle'], $banksettings['bankpbclean'], $banksettings['bankcurrency'] );
		} else {
			$forums->lang['transferdesc'] = sprintf( $forums->lang['transferdesc'], $banksettings['bankexcost'], $banksettings['bankexcostskip'], $banksettings['bankcurrency'] );
			$thisuserinfo['cash'] = $forums->func->fetch_number_format($bbuserinfo['cash']);
			$thisuserinfo['bank'] = $forums->func->fetch_number_format($bbuserinfo['bank']);
			$canloan = $this->can_loan();
		}
		$statinfo = $DB->query_first("SELECT COUNT(u.id) as clients, SUM(u.bank)-SUM(e.loanamount) as deposit FROM ".TABLE_PREFIX."user u
							  LEFT JOIN ".TABLE_PREFIX."userextra e ON (u.id = e.id) WHERE u.mkaccount > 0");
		if ( $statinfo['deposit'] == NULL ) {
			$statinfo['deposit'] = 0;
		}
		$statinfo['deposit'] = $forums->func->fetch_number_format($statinfo['deposit']);
		$statinfo['clients'] = $forums->func->fetch_number_format($statinfo['clients']);
		$DB->query("SELECT b.*, f.name AS fromname, t.name AS toname FROM ".TABLE_PREFIX."banklog b
		LEFT JOIN ".TABLE_PREFIX."user f ON (b.fromuserid = f.id)
		LEFT JOIN ".TABLE_PREFIX."user t ON (b.touserid = t.id)
			WHERE b.fromuserid=".$bbuserinfo['id']." OR b.touserid=".$bbuserinfo['id']." ORDER BY id DESC LIMIT 0, 5");
		if ($DB->num_rows()) {
			while ($row = $DB->fetch_array()) {
				if ($row['fromuserid'] == $row['touserid']) {
					$row['type'] = "";
				} elseif ($row['fromuserid'] == $bbuserinfo['id']) {
					$row['type'] = $forums->lang['transfer_out'];
				} else {
					$row['type'] = $forums->lang['transfer_in'];
				}
				$row['fromname'] = $row['fromname'] ? "<a href='profile.php?{$forums->sessionurl}u={$row['fromuserid']}' target='_blank'>{$row['fromname']}</a>" : $forums->lang['system_log'];
				$row['toname'] = $row['toname'] ? "<a href='profile.php?{$forums->sessionurl}u={$row['touserid']}' target='_blank'>{$row['toname']}</a>" : $forums->lang['system_log'];
				$row['dateline'] = $forums->func->get_date($row['dateline'], 2);
				$banklog[] = $row;
			}
		}
		if ( !$banksettings['bankloanonoff'] ) {
			include $forums->func->load_template('bank_main');
			exit;
		}
		$forums->lang['loanexplaincondition3'] = sprintf( $forums->lang['loanexplaincondition3'], intval($banksettings['bankloanreglimit']), intval($banksettings['bankloanpostlimit']), intval($banksettings['bankloanreplimit']) );
		$forums->lang['loanexplaincondition5'] = sprintf( $forums->lang['loanexplaincondition5'], intval($banksettings['bankpbrerate']) );
		$forums->lang['loanexplaincondition6'] = sprintf( $forums->lang['loanexplaincondition6'], $bboptions['bbtitle'] );
		if ( !$bbuserinfo['loanreturn'] ) {
			$loantypelist = $this->mkloantypelist();
		} else {
			$thisuserinfo['loaninfo']['timereturn'] = $forums->func->get_date($bbuserinfo['loanreturn'], 3);
			$thisuserinfo['loaninfo']['amount'] = $forums->func->fetch_number_format($bbuserinfo['loanamount']);
			$thisuserinfo['loaninfo']['interest'] = $bbuserinfo['loaninterest'];
			$thisuserinfo['loaninfo']['interestnum'] = $this->bankfunc->calculate_interest($bbuserinfo['loanamount'], $bbuserinfo['loaninterest']);
			$thisuserinfo['loaninfo']['interestnum'] = $forums->func->fetch_number_format($thisuserinfo['loaninfo']['interestnum']);
			$thisuserinfo['loaninfo']['moneyreturn'] = $this->bankfunc->calculate_interest($bbuserinfo['loanamount'], 1000+$bbuserinfo['loaninterest']);
			$thisuserinfo['loaninfo']['moneyreturn'] = $forums->func->fetch_number_format($thisuserinfo['loaninfo']['moneyreturn']);
			$thisuserinfo['loaninfo']['timeleft'] = intval(($bbuserinfo['loanreturn']-TIMENOW)/86400);
			if ( ($bbuserinfo['loanreturn']-TIMENOW) % 86400  )
				$thisuserinfo['loaninfo']['timeleft']++;
		}
		include $forums->func->load_template('bank_loan');
		exit;
	}

	function transfer()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$amount = intval($_INPUT['amount']);
		$_INPUT['target'] = preg_replace( "/\s{2,}/", " ", trim( str_replace( '|', '&#124;' , $forums->func->strtolower($_INPUT['target']) ) ) );
		if ( !$bbuserinfo['mkaccount'] OR $bbuserinfo['mkaccount'] == -1 ) {
			$forums->func->standard_error("notcreateaccount");
		}
		if ( $amount < 1 ) {
			$forums->func->standard_error("noamountmoney");
		}
		if ( !$_INPUT['target'] ) {
			$forums->func->standard_error("noamountuser");
		}
		if ( $_INPUT['target'] == $forums->func->strtolower($bbuserinfo['name'])) {
			$forums->func->standard_error("cannottransferself");
		}
		if ( $bbuserinfo['bank'] < $amount ) {
			$forums->func->standard_error("noenoughdeposit");
		}
		$banksettings = $this->banksettings;
		$transfercost = $this->bankfunc->calculate_interest($amount, $banksettings['bankexcost']);
		if ( $amount >= $banksettings['bankexcostskip'] AND $bbuserinfo['bank'] < ($amount+$transfercost) ) {
			$forums->func->standard_error("nobankexcost");
		}
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		if ( $bbuserinfo['loanamount'] ) {
			$forums->func->standard_error("loanamount");
		}
		if ( $amount < $banksettings['bankexcostskip'] ) {
			$transfercost = 0;
		}
		$taruser = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($_INPUT['target'])."' OR name='".$_INPUT['target']."'");
		if ( !$taruser['id'] ) {
			$forums->func->standard_error("noamountuser");
		}
		$taruser = $this->bankfunc->patch_bankinfo($taruser);
		if ( !$taruser['mkaccount'] OR $taruser['mkaccount'] == -1 ) {
			$forums->func->standard_error("usernoaccount");
		}

		$this->bankfunc->trdesc = $forums->lang['virement'] . ': ' . $bbuserinfo['name'] . ' > ' . $taruser['name'] . ' (' . $forums->lang['money']. ': ' . $amount.$banksettings['bankcurrency'];
		if ( $transfercost ) {
			$this->bankfunc->trdesc .= ', '.$forums->lang['cost'].': '.$transfercost.$banksettings['bankcurrency'];
		}
		$this->bankfunc->trdesc .= ')';
		$this->bankfunc->fromCorB = 1;
		$this->bankfunc->tarCorB = 1;
		$this->bankfunc->meextra = -1*$transfercost;
		$this->bankfunc->tarextra = 0;

		$actionresult = $this->bankfunc->user_transfer_money($taruser, $amount);
		if ( $actionresult != 1 ) {
			$forums->func->standard_error($actionresult);
		}
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function takeloan()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$amount = intval($_INPUT['amount']);
		$days = intval($_INPUT['days']);
		if ( !$_INPUT['loaninterest'] || !$days || !intval($_INPUT['loaninterest']) || $_INPUT['loaninterest'] < 0 || $amount < 1 ) {
			$forums->func->standard_error("plzinputallform");
		}
		if ( !$_INPUT['agreetoterms'] ) {
			$forums->func->standard_error("mustreadstatement");
		}
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		if ( $bbuserinfo['loanreturn'] ) {
			$forums->func->standard_error("alreadyloan");
		}
		if ( !$this->can_loan() ) {
			$forums->func->standard_error("cannotloan");
		}
		$usercredit = $this->calculate_credit();
		if ( $amount > $usercredit['creditamount'] ) {
			$forums->func->standard_error("nocreditamount");
		}
		$sql_bank = $bbuserinfo['bank'] + $amount;
		$sql_loanreturn = TIMENOW + $_INPUT['days']*86400;
		$sql_loaninterest = $_INPUT['loaninterest'];
		$ext = $DB->query_first("SELECT * FROM ".TABLE_PREFIX."userextra WHERE id = ".$bbuserinfo['id']);
		if ($ext)
		{
			$sql = "UPDATE ".TABLE_PREFIX."userextra SET loanamount = ".$amount.", loanreturn = ".$sql_loanreturn.", loaninterest = ".$sql_loaninterest." WHERE id = ".$bbuserinfo['id']."";
		}
		else
		{
			$sql = "INSERT INTO ".TABLE_PREFIX."userextra (id, loanamount, loanreturn,loaninterest) VALUES (".$bbuserinfo['id'].", ".$amount.", ".$sql_loanreturn.",".$sql_loaninterest.")";
		}
		if (!$DB->query_unbuffered($sql))
		{
			$forums->func->standard_error("handleerror");
		}
		if ( !$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET bank = ".$sql_bank." WHERE id = ".$bbuserinfo['id']."")) {
			$forums->func->standard_error("handleerror");
		}
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function returnloan()
	{
		global $forums, $DB, $bbuserinfo;
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		if ( !$bbuserinfo['loanreturn'] ) {
			$forums->func->standard_error("noloan");
		}
		if ( !$this->can_loan() ) {
			$forums->func->standard_error("cannotpayloan");
		}
		$moneytoreturn = $this->bankfunc->calculate_interest($bbuserinfo['loanamount'], 1000+$bbuserinfo['loaninterest']);
		if ( $bbuserinfo['bank'] < $moneytoreturn ) {
			$forums->func->standard_error("nobankexcost");
		}
		if ( !$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET bank = bank-".$moneytoreturn." WHERE id = ".$bbuserinfo['id']."")
		     || !$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."userextra SET loanamount = 0, loanreturn = 0, loaninterest = 0 WHERE id = ".$bbuserinfo['id']."") ) {
			$forums->func->standard_error("handleerror");
		}
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function depositorwithdraw()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$amount = intval($_INPUT['amount']);
		if ( $_INPUT['dorw'] != 'd' AND $_INPUT['dorw'] != 'w' ) {
			$forums->func->standard_error("erroroperation");
		}
		if ( $amount < 1 ) {
			$forums->func->standard_error("noamountmoney");
		}
		if ( $_INPUT['dorw'] == 'd' AND $bbuserinfo['cash'] < $amount ) {
			$forums->func->standard_error("noenoughmoney");
		}
		if ( !$bbuserinfo['mkaccount'] ) {
			$forums->func->standard_error("notcreateaccount");
		}
		if ( $bbuserinfo['mkaccount'] == -1 ) {
			$forums->func->standard_error("noclearbankruptcy");
		}
		if ( $_INPUT['dorw'] == 'w' ) {
			if ( $bbuserinfo['bank'] < $amount ) {
				$forums->func->standard_error("nobankexcost");
			}
			$bbuserinfo = $this->bankfunc->patch_bankinfo();
			if ( $bbuserinfo['loanamount'] ) {
				$forums->func->standard_error("loanamount");
			}
		}
		$this->bankfunc->dorw = $_INPUT['dorw'];
		$this->bankfunc->bank_transfer_money($amount);
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function borsreputation()
	{
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$amount = intval($_INPUT['amount']);
		if ( !$_INPUT['bors'] || ($_INPUT['bors'] != 'b' && $_INPUT['bors'] != 's') ) {
			$forums->func->standard_error("erroroperation");
		}
		if ( $amount < 1 ) {
			$forums->func->standard_error("noamountmoney");
		}
		$banksettings = $this->banksettings;
		if ( $_INPUT['bors'] == 'b' && $bbuserinfo['cash'] < $amount*$banksettings['bankrepprice'] ) {
			$forums->func->standard_error("noenoughmoney");
		}
		if ( $_INPUT['bors'] == 's' && $bbuserinfo['reputation'] < $amount ) {
			$forums->func->standard_error("noenoughreputation", 0, $amount);
		}
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		if ( $_INPUT['bors'] == 'b' ) {
			$this->bankfunc->desc = $forums->lang['buy']." ".$amount." ".$forums->lang['reputationpoint'];
			if ( !$this->bankfunc->add_money($bbuserinfo, -1*$amount*$banksettings['bankrepprice']) ) {
				$forums->func->standard_error("handleerror");
			} else {
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET reputation = reputation+".$amount." WHERE id = ".$bbuserinfo['id']."");
			}
		} else if ( $_INPUT['bors'] == 's' ) {
			$this->bankfunc->desc = $forums->lang['sale']." ".$amount." ".$forums->lang['reputationpoint'];
			if ( !$this->bankfunc->add_money($bbuserinfo, $amount*$banksettings['bankrepsellprice']) ) {
				$forums->func->standard_error("handleerror");
			} else {
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET reputation = reputation-".$amount." WHERE id = ".$bbuserinfo['id']."");
			}
		}
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function showlog()
	{
        global $forums, $DB, $bbuserinfo, $bboptions, $_INPUT;
		$pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 0;
		$userid = intval($_INPUT['u']);
		if ($_INPUT['username']) {
			$username = trim($_INPUT['username']);
			$user = $DB->query_first("SELECT id FROM ".TABLE_PREFIX."user WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'");
			if ($user['id']) {
				$userid = $user['id'];
			}
		}
		if (($userid > 0 OR $_INPUT['showall']) AND $bbuserinfo['supermod']) {
			if ($userid) {
				$extra_links = "&amp;u=$userid";
				$where = "fromuserid = {$userid} OR touserid = {$userid}";
			} else {
				$extra_links = "&amp;showall=1";
				$where = "";
			}
		} else {
			$where = "fromuserid = {$bbuserinfo['id']} OR touserid = {$bbuserinfo['id']}";
		}
		if ($where) {
			$where = "WHERE ".$where;
		}

		$logscount = $DB->query_first("SELECT COUNT(id) AS count FROM ".TABLE_PREFIX."banklog $where");

		$pagelink = $forums->func->build_pagelinks(
								array( 'totalpages'  => $logscount['count'],
											'perpage'    => 30,
											'curpage'  => $pp,
											'pagelink'    => "bank.php?{$forums->sessionurl}do=showlog{$extra_links}",
										  )
								   );

		$DB->query("SELECT b.*, f.name AS fromname, t.name AS toname FROM ".TABLE_PREFIX."banklog b
		LEFT JOIN ".TABLE_PREFIX."user f ON (b.fromuserid = f.id)
		LEFT JOIN ".TABLE_PREFIX."user t ON (b.touserid = t.id)
			$where ORDER BY id DESC LIMIT {$pp}, 30");
		if ($DB->num_rows()) {
			while ($row = $DB->fetch_array()) {
				if ($row['fromuserid'] == $row['touserid']) {
					$row['type'] = "";
				} elseif ($row['fromuserid'] == $bbuserinfo['id']) {
					$row['type'] = $forums->lang['transfer_out'];
				} else {
					$row['type'] = $forums->lang['transfer_in'];
				}
				$row['fromname'] = $row['fromname'] ? "<a href='profile.php?{$forums->sessionurl}u={$row['fromuserid']}' target='_blank'>{$row['fromname']}</a>" : $forums->lang['system_log'];
				$row['toname'] = $row['toname'] ? "<a href='profile.php?{$forums->sessionurl}u={$row['touserid']}' target='_blank'>{$row['toname']}</a>" : $forums->lang['system_log'];
				$row['dateline'] = $forums->func->get_date($row['dateline'], 2);
				$banklog[] = $row;
			}
		}
        $pagetitle = $forums->lang['user_bank_log'];
		include $forums->func->load_template('bank_showlog');
		exit;
	}

	function mkaccount()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$banksettings = $this->banksettings;
		$thisuserinfo = $this->fetch_userinfo();
		$toprich = $this->generate_toprich();
		if ( $bbuserinfo['cash'] < $banksettings['bankcost'] ) {
			$forums->func->standard_error("noenoughmoney");
		}
		if ( $bbuserinfo['mkaccount'] > 0 ) {
			$forums->func->standard_error("alreadyaccount", 0, $thisuserinfo['mkaccount']);
		}
		if ( $bbuserinfo['mkaccount'] == -1 ) {
			$forums->func->standard_error("noclearbankruptcy");
		}
		$this->bankfunc->desc = $forums->lang['accountamount'];
		$this->bankfunc->add_money($bbuserinfo, -1*$banksettings['bankcost'], 0, TRUE);
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function purge()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$banksettings = $this->banksettings;
		$pagetitle = $forums->lang['bank'].' -> '.$bboptions['bbtitle'];
 		$nav = array( "<a href='bank.php?{$forums->sessionurl}'>" . $forums->lang['bank']."</a>" );
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		$thisuserinfo = $this->fetch_userinfo();
		$toprich = $this->generate_toprich();
		$statinfo = $DB->query_first("SELECT COUNT(u.id) as clients, SUM(u.bank)-SUM(e.loanamount) as deposit FROM ".TABLE_PREFIX."user u
					      LEFT JOIN ".TABLE_PREFIX."userextra e ON (u.id = e.id) WHERE u.mkaccount > 0");
		if ( $statinfo['deposit'] == NULL ) {
			$statinfo['deposit'] = 0;
		}
		$statinfo['deposit'] = $forums->func->fetch_number_format($statinfo['deposit']);
		$statinfo['clients'] = $forums->func->fetch_number_format($statinfo['clients']);
		$thisuserinfo['cash'] = $forums->func->fetch_number_format($bbuserinfo['cash']);
		$thisuserinfo['bank'] = $forums->func->fetch_number_format($bbuserinfo['bank']);
		$forums->lang['cleaninfo'] = sprintf( $forums->lang['cleaninfo'], $banksettings['bankcost'], $banksettings['bankcurrency'], $banksettings['bankcurrency'] );

		if ( !$bbuserinfo['mkaccount'] || $bbuserinfo['mkaccount'] == -1 ) {
			$forums->func->standard_error("notcreateaccount");
		}
		if ( $bbuserinfo['loanamount'] ) {
			$forums->func->standard_error("alreadyloan");
		}
		include $forums->func->load_template('bank_purge');
		exit;
	}

	function dopurge()
	{
		global $forums, $DB, $bbuserinfo;
		$bbuserinfo = $this->bankfunc->patch_bankinfo();
		if ( !$bbuserinfo['mkaccount'] || $bbuserinfo['mkaccount'] == -1 ) {
			$forums->func->standard_error("notcreateaccount");
		}
		if ( $bbuserinfo['loanamount'] ) {
			$forums->func->standard_error("alreadyloan");
		}
		$this->bankfunc->dorw = "w";
		if ( $bbuserinfo['bank'] > 0 ) {
			$this->bankfunc->bank_transfer_money($bbuserinfo['bank']);
		}
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET mkaccount = '' WHERE id = ".$bbuserinfo['id']);
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}

	function pbclean()
	{
		global $forums, $DB, $bbuserinfo, $bboptions;
		$banksettings = $this->banksettings;
		$thisuserinfo = $this->fetch_userinfo();
		$toprich = $this->generate_toprich();
		if ( $bbuserinfo['mkaccount'] != -1 ) {
			$forums->func->standard_error("nobankruptcy");
		}
		if ( $bbuserinfo['cash'] < $banksettings['bankpbclean'] ) {
			$forums->func->standard_error("noenoughmoney");
		}
		$this->bankfunc->desc = $forums->lang['cleanamount'];
		$this->bankfunc->add_money($bbuserinfo, -1*$banksettings['bankpbclean']);
		$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET mkaccount = 0 WHERE id = ".$bbuserinfo['id']."");
		$forums->func->standard_redirect("bank.php?".$forums->sessionurl);
		exit;
	}
}

$output = new bank();
$output->show();

?>