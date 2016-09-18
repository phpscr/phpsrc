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
define('THIS_SCRIPT', 'mxajax');

require_once('./global.php');

class mxajax{

	var $canview_hidecontent = 0;
	var $already_replied = 0;
	var $canupload = 0;
	var $postcount = 0;
	var $obj =array();
	var $cookie_mxeditor = "wysiwyg";

	function show(){
		global $forums, $DB, $_INPUT, $bboptions;
		$forums->forum->forums_init();
		$this->mxajax_export('changetext', 'change_value', 'returnsig', 'rep', 'quickreply', 'returntext', 'switchtext','checkuser','checkmail','change_name', 'openthread', 'closethread', 'cbsend', 'sendattach','removeattach','returnpagetext','deletepost','changerule','change_cash','sendpreview','smilespage');
		$this->mxajax_handle_client_request();
	}

	function mxajax_init() {}

	function mxajax_handle_client_request() {
		global $mxajax_export_list;
		$mode = "";
		if (! empty($_GET["rs"]))
		$mode = "get";
		if (!empty($_POST["rs"]))
		$mode = "post";
		if (empty($mode))
		return;
		if ($mode == "get") {
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			$func_name = $_GET["rs"];
			if (! empty($_GET["rsargs"]))
			$args = $_GET["rsargs"];
			else
			$args = array();
		} else {
			$func_name = $_POST["rs"];
			if (! empty($_POST["rsargs"]))
			$args = $_POST["rsargs"];
			else
			$args = array();
		}

		if (! in_array($func_name, $mxajax_export_list))
		echo "-:$func_name not callable";
		else {
			switch($func_name) {
				case 'switchtext':
					$this->switchtext($args);
					break;
				case 'change_value':
					$this->change_value($args);
					break;
				case 'returnsig':
					$this->returnsig();
					break;
				case 'rep':
					$this->rep($args);
					break;
				case 'quickreply':
					$this->quickreply($args);
					break;
				case 'returntext':
					$this->returntext();
					break;
				case 'change_name':
					$this->change_name($args);
					break;
				case 'closethread':
					$this->closethread($args);
					break;
				case 'openthread':
					$this->openthread($args);
					break;
				case 'checkuser':
					$this->checkuser($args);
					break;
				case 'checkmail':
					$this->checkmail($args);
					break;
				case 'cbsend':
					$this->cbsend($args);
					break;
				case 'sendattach':
					$this->sendattach($args);
					break;
				case 'removeattach':
					$this->removeattach($args);
					break;
				case 'returnpagetext':
					$this->returnpagetext($args);
					break;
				case 'deletepost':
					$this->deletepost($args);
					break;
				case 'changerule':
					$this->changerule($args);
					break;
				case 'change_cash':
					$this->change_cash($args);
					break;
				case 'sendpreview':
					$this->sendpreview($args);
					break;
				case 'smilespage':
					$this->smilespage($args);
					break;
			}
		}
		exit;
	}

	function mxajax_export(){
		global $mxajax_export_list;
		$n = func_num_args();
		for ($i = 0; $i < $n; $i++) {
			$mxajax_export_list[] = func_get_arg($i);
		}
	}

	function switchtext($n){
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		foreach ($_POST['rsargs'] as $value){
			$posttext[] = $value;
		}

		if ($posttext[2] == 'signature') {
			$allowcode = intval($bboptions['signatureallowbbcode']);
			$allowhtml = intval($bboptions['signatureallowhtml']);
		} else if ($posttext[2] == 'pm') {
			$allowcode = intval($bboptions['pmallowbbcode']);
			$allowhtml = intval($bboptions['pmallowhtml']);
		} else {
			$fid = intval($posttext[2]);
			if ($fid > 0 ){
				$forum = $forums->forum->single_forum($fid);
				$allowcode = $forum['allowbbcode'];
				$allowhtml = $forum['allowhtml'] && $bbuserinfo['canposthtml'];
			} else {
				$allowcode = 1;
				$allowhtml = 0;
			}
		}

		$htmlvalue = $forums->func->stripslashes_uni($posttext[0]);

		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();

		if ($posttext[1] == 0) {
			$text = $lib->convert(array (
				'text' => $forums->func->htmlspecialchars_uni($htmlvalue),
				'allowsmilies' => intval($posttext[3]),
				'allowcode' => $allowcode,
				'allowhtml' => $allowhtml,
				'usewysiwyg' => 1,
				'change_editor' => 1,)
			);
			echo $text;
		} else {
			$postvalue = $lib->unconvert($htmlvalue, $allowcode, $allowhtml, 0, 1);
			$postvalue = preg_replace("#<br.*>#siU", "\n", $postvalue);
			echo $postvalue;
		}
	}

	function change_value($n){
		global $DB;
		$getpost = $DB->query_first( "SELECT pagetext FROM ".TABLE_PREFIX."post WHERE pid=".intval($n)."" );
		return $getpost['pagetext'];
	}

	function returnsig()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		foreach ($_POST['rsargs'] as $value){
			$info[] = $value;
		}
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$info[1] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $info[1]);
		$forum =$forums->forum->single_forum( $post['forumid'] );
		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();
		if ( $info[3] ) {
			$bbuserinfo['usewysiwyg'] = ($info[3] == 'wysiwyg') ? 1 : 0;
		} else {
			$bbuserinfo['usewysiwyg'] = ($bboptions['mxemode']) ? 1 : 0;
		}
		$info['1'] = $bbuserinfo['usewysiwyg'] ? $info['1'] : $forums->func->htmlspecialchars_uni($info['1']);
		$info['1'] = $lib->censoredwords($info['1']);
		$sig = $lib->convert(array(
			'text' => $forums->func->stripslashes_uni($info['1']),
			'allowsmilies' => 1,
			'allowcode' => $bboptions['signatureallowbbcode'],
			'allowhtml' => $bboptions['signatureallowhtml'],
			'usewysiwyg' => $bbuserinfo['usewysiwyg'])
		);
		if ($bbuserinfo['id']==$info[2] || $bbuserinfo['supermod']) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET signature = '".addslashes($sig)."' WHERE  id = '".$info['2']."'");
			echo $info[0]."]:::[".str_replace('%', '%25', $sig);
		}
	}

	function rep($n)
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate');
		$forums->lang = $forums->func->load_lang($forums->lang, 'error');
		$value = explode("]:::[", $n[0]);
		$postuid = intval($value[0]);
		$tid = intval($value[1]);
		$pid = intval($value[2]);
		$num = intval($value[3]);
		$reputation['user'] = $bbuserinfo['name'];
		$reputation['number'] = $num;
		$pid = intval($pid);

		if ($postuid == $bbuserinfo['id']) {
			echo $pid."]:::[".$forums->lang['modrep_sameuserid_error'];
			exit;
		}
		$tarinfo = $DB->query_first("SELECT p.reppost,p.threadid,u.id, u.name, u.usergroupid
			FROM ".TABLE_PREFIX."post p
				LEFT JOIN ".TABLE_PREFIX."user u ON (p.userid = u.id)
				LEFT JOIN ".TABLE_PREFIX."usergroup g ON (g.usergroupid = u.usergroupid)
			WHERE p.pid = $pid");
		if (!$tarinfo['id']) {
			echo $pid."]:::[".$forums->lang["cannotfindedituser"];
			exit;
		}
		if (!preg_match('/,' . $tarinfo['usergroupid'] . ',/i', ',' . $bbuserinfo['canmodrep'] . ',')) {
			echo $pid."]:::[".$forums->lang["cannotrep"];
			exit;
		}
		if ($num != 0) {
			$row = $DB->query_first("SELECT reppost FROM ".TABLE_PREFIX."post WHERE pid = $pid");
			$rep = unserialize($row['reppost']);
			if($rep){
				echo $pid."]:::[".$forums->lang['_reperror'];
				exit;
			}
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET reputation = reputation+$num WHERE id = ".$postuid." LIMIT 1");
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET reppost = '".addslashes(serialize($reputation))."' WHERE pid = $pid");
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allrep = allrep+$num WHERE tid = $tid");
			$str = $forums->lang['_showreputation'].": $num | ".$forums->lang['_repuser'].": ".$bbuserinfo['name'];
			require(ROOT_PATH."includes/functions_moderate.php");
			$modlog = new modfunctions();
			if ($num > 0) {
				$message = sprintf($forums->lang['makequintessenceplus'], $num);
				$title = sprintf($forums->lang['makequintessencepmplus'], $num);
			} else {
				$message = sprintf($forums->lang['makequintessenceminus'], $num);
				$title = sprintf($forums->lang['makequintessencepmminus'], $num);
			}
			$modlog->add_moderate_log('0',$tid,$pid,'',$message);
			$message = $message.": <a href='showthread.php?t=$tid'>".$forums->lang['thisthread']."</a>";
			$this->sendpm($message,$title,$postuid);
			echo $pid."]:::[".$str;
		} else {
			$reppost = $DB->query_first("SELECT reppost FROM ".TABLE_PREFIX."post WHERE pid = '".intval($pid)."'");
			if(!$reppost['reppost']) {
				echo $pid."]:::[".$forums->lang['_norep'];
				exit;
			}
			$rep = unserialize($reppost['reppost']);
			if($rep) {
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."post SET reppost = '' WHERE pid = ".intval($pid)."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET reputation = reputation-".intval($rep['number'])." WHERE id = ".intval($postuid)." LIMIT 1");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allrep = allrep-".intval($rep['number'])." WHERE tid = ".$tid."");
				require(ROOT_PATH."includes/functions_moderate.php");
				$modlog = new modfunctions();
				$modlog->add_moderate_log('0',$tid,$pid,'',$forums->lang['makeunquintessenceinit']);
				$message = $forums->lang['makeunquintessenceinit'].": <a href='showthread.php?t=".$tid."'>".$forums->lang['thisthread']."</a>";
				$title = $forums->lang['unquintessencethreadpm'];
				$this->sendpm($message,$title,$postuid);
				echo $pid."]:::[".$forums->lang['_repvar'];
			}
		}
	}

	function quickreply($n)
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		if ($bboptions['newregisterpost'] ) {
			if ( (TIMENOW - $bboptions['newregisterpost']*60) < $bbuserinfo['joindate'] ) {
				$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
				echo "1]:::[".sprintf($forums->lang["newregisterpost"] , $bboptions['newregisterpost']);
				exit;
			}
		}
		if ( $bboptions['floodchecktime'] > 0 ) {
			if (!$bbuserinfo['passflood']) {
				if ( TIMENOW - $bbuserinfo['lastpost'] < $bboptions['floodchecktime'] ) {
					$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
					echo "1]:::[".sprintf($forums->lang["floodcheck"] , $bboptions['floodchecktime']);
					exit;
				}
			}
		}
		$forums->lang = $forums->func->load_lang( $forums->lang, 'showthread' );
		$forums->lang = $forums->func->load_lang( $forums->lang, 'post' );
		$lang = $forums->lang;
		$forum = $forums->forum->single_forum( $_POST['rsargs'][0] );
		$_POST['rsargs'][4] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $_POST['rsargs'][4]);
		$value = array(0 => $_POST['rsargs'][4],1 => '',2 => $_INPUT['rsargs'][5],3 => $_INPUT['rsargs'][0],4 => $_INPUT['rsargs'][3],5 => $_INPUT['rsargs'][14],6 => $_INPUT['rsargs'][1],7 => $_INPUT['rsargs'][6],8 => $_INPUT['rsargs'][7],9 => $_INPUT['rsargs'][8],10 => $_INPUT['rsargs'][9],11 => $_INPUT['rsargs'][10],12 => $_INPUT['rsargs'][11],13 => $_INPUT['rsargs'][12],14 => $_INPUT['rsargs'][2],15 => $_INPUT['rsargs'][13]);
		$nump = 10;
		$sendbutton = array('f' => $_INPUT['rsargs'][0], 't' => $_INPUT['rsargs'][3] );
		require(ROOT_PATH."includes/functions_post.php");
		$this->lib = new functions_post();
		$this->lib->forum = $forum;
		$thread = $DB->query_first( "SELECT * FROM ".TABLE_PREFIX."thread WHERE tid='".intval($value[4])."'");
		if (!$this->check_permission($thread)) {
			$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
			echo "1]:::[".$forums->lang["cannotreply"];
			exit;
		}
		require(ROOT_PATH."includes/functions_credit.php");
		$this->credit = new functions_credit();
		$this->credit->check_credit_limit('newreply');
		if ($bboptions['useantispam']) {
			$antispam = $DB->query_first("SELECT imagestamp FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".addslashes(trim($value[6]))."'");
			if ($antispam['imagestamp'] != $value[15]) {
				echo "1]:::[".$forums->lang["_imagehasherror"];
				exit;
			}
			$DB->query_unbuffered("DELETE FROM ".TABLE_PREFIX."antispam WHERE regimagehash='".addslashes(trim($value[6]))."'");
		}else{
			$antispam['text'] = 1;
			$antispam['imagehash'] = 1;
		}
		$_INPUT['t'] = $_INPUT['rsargs'][3];
		$quote = $this->lib->check_multi_quote(0);
		if ($quote) {
			$value[0] = $quote.$value[0];
		}
		$info['userid'] = $bbuserinfo['id'];
		$info['showsignature'] = $value[8];
		$info['allowsmile'] = $value[7];
		$info['host'] = SESSION_HOST;
		$info['dateline'] = TIMENOW;
		$obj['moderate'] = intval($forum['moderatepost']);
		if ($bbuserinfo['passmoderate']) {
			$obj['moderate'] = 0;
		}
		if ( $bbuserinfo['moderate'] ) {
			if ( $bbuserinfo['moderate'] == 1 ) {
				$obj['moderate'] = 1;
			} else {
				$mod_arr = $forums->func->banned_detect( $bbuserinfo['moderate'] );
				if ( TIMENOW >= $mod_arr['date_end'] ) {
					$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET moderate=0 WHERE id=".$bbuserinfo['id']."" );
					$obj['moderate'] = intval($forum['moderatepost']);
				} else {
					$obj['moderate'] = 1;
				}
			}
		}
		if ( $_INPUT['rsargs'][15] ) {
			$bbuserinfo['usewysiwyg'] = ($_INPUT['rsargs'][15] == 'wysiwyg') ? 1 : 0;
		} else {
			$bbuserinfo['usewysiwyg'] = ($bboptions['mxemode']) ?1 : 0;
		}
		$post = $bbuserinfo['usewysiwyg'] ? $value[0] : $forums->func->htmlspecialchars_uni($value[0]);
		$post = $this->lib->parser->censoredwords($post);
		$content = $this->lib->parser->convert(array(
			'text' => $forums->func->stripslashes_uni($post),
			'allowsmilies' => $value[7],
			'allowcode' => $forum['allowbbcode'],
			'allowhtml' => $forum['allowhtml'],
			'usewysiwyg' => $bbuserinfo['usewysiwyg'])
		);

		$info['pagetext'] = $content;
		$info['username'] = $bbuserinfo['name'];
		$info['threadid'] = $thread['tid'];
		if ($bbuserinfo['cananonymous'] && $_INPUT['rsargs'][16]) {
			$_INPUT['anonymous'] = $info['anonymous'] = 1;
			$lastpost_sql = ", lastposterid = 0, lastposter = 'anonymous*'";
		} else {
			$_INPUT['anonymous'] = $info['anonymous'] = 0;
			$lastpost_sql = ", lastposterid = ".$bbuserinfo['id'].", lastposter = '".addslashes($bbuserinfo['name'])."'";
		}
		$info['posthash'] = $value[6];
		$info['moderate'] = ($obj['moderate'] == 1 || $obj['moderate'] == 3) ? 1 : 0;
		$user['avatar'] = $forums->func->get_avatar($bbuserinfo['avatarlocation'], $bbuserinfo['showavatars'], $bbuserinfo['avatarsize'], $bbuserinfo['avatartype']);
		if ($user['avatar']){
			$avatar = $user['avatar'];
		}else{
			$avatar = '';
		}
		$forums->func->fetch_query_sql($info, 'post');
		$divinfo['id'] = $DB->insert_id();
		$this->lib->obj['moderate'] = $obj['moderate'];
		$this->lib->stats_recount($thread['tid'], $thread['title'], 'reply');

		$addcash = $value[11] ? $value[11] : 0;
		$forums->func->check_cache('banksettings');
		if ($addcash) {
			$addcash = explode("|", $addcash);
			$postaddcash = $addcash[1];
		} else {
			if ($forums->cache['banksettings']['bankreplythread']) {
				$postaddcash = $forums->cache['banksettings']['bankreplythread'];
			} else {
				$postaddcash = $addcash;
			}
		}
		$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."user SET lastpost = ".$info['dateline'].", posts = posts+1, cash = cash+".intval($postaddcash)." WHERE id = '".$bbuserinfo['id']."';" );
		$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."thread SET lastpost = '".$info['dateline']."', post = post+1$lastpost_sql, lastpostid =".$divinfo['id']." WHERE  tid = '".$thread['tid']."';" );
		if ($thread['sticky'] == '2') {
			$forums->func->recache_cache('globalstick');
		}
		$hideposts = $DB->query( "SELECT pid, userid, hidepost FROM ".TABLE_PREFIX."post WHERE threadid='".$thread['tid']."' AND hidepost!=''" );
		if ($DB->num_rows($hideposts) ) {
			while ($hidepost=$DB->fetch_array($hideposts)) {
				$hideinfo = unserialize($hidepost['hidepost']);
				if ( $hideinfo['type'] == '111' AND $hidepost['userid'] != $bbuserinfo['id']) {
					if (is_array($hideinfo['buyers']) AND in_array($bbuserinfo['name'], $hideinfo['buyers']) ) continue;
					$hideinfo['buyers'][] = $bbuserinfo['name'];
					$DB->shutdown_query( "UPDATE ".TABLE_PREFIX."post SET hidepost='".addslashes(serialize($hideinfo))."' WHERE pid='".$hidepost['pid']."'" );
				}
			}
		}
		$this->credit->update_credit('newreply', $bbuserinfo['id']);
		if ($info['moderate']) {
			echo "1]:::[".$forums->lang["hasajaxpost"];
			exit;
		}
		foreach ($_INPUT['rsargs'] AS $rows) {
			$value[] = $rows;
		}
		$divnum= $value[14];
		$user['avatar'] = $forums->func->get_avatar( $bbuserinfo['avatarlocation'], $bbuserinfo['showavatars'], $bbuserinfo['avatarsize'], $bbuserinfo['avatartype'] );
		if ($user['avatar']){
			$avatar = $user['avatar'];
		} else {
			$avatar = "";
		}
		$antispam = $this->lib->code->showantispam();

		$bbuserinfo['lastpost'] = $info['dateline'];
		$bbuserinfo['posts'] += 1;
		$bbuserinfo['cash'] += $postaddcash;

		$this_post = $bbuserinfo;
		$this_post['pagetext'] = str_replace(array('[', ']', '%'), array('&#91;', '&#93;', '%25'), $info['pagetext']);
		$this_post['pid'] = $divinfo['id'];
		$this_post['threadid'] = $thread['tid'];
		$this_post['host'] = SESSION_HOST;
		$this_post['dateline'] = TIMENOW;

		$forums->func->check_cache('usergroup');
		$return = $this->parse_row( $this_post , $sendbutton );
		$return['poster']['cash'] = $return['poster']['cash'].$forums->cache['banksettings']['bankcurrency'];
		$return['poster']['status'] = 1;
		$return['row']['name_css'] = "normalname";
		$return['poster']['onlinerankimg'] = array();
		$showpost[] =  $return;

		$divnext = $divnum+1;
		$forum['id'] = $_POST['rsargs']['0'];
		$thread['tid'] = $_POST['rsargs']['3'];
		$thisfid = $forum['id'];
		if (($bbuserinfo['_moderator'][$thisfid]['caneditposts'] OR $bbuserinfo['supermod'])) {
			$ismod_or_supermod = true;
			if ($bboptions['isajax']) {
				$canajaxeditpost = true;
			}
		}
		echo $divnum."]:::[";
		include $forums->func->load_template('showthread_post');
		echo "<div id=\"ajaxrep".$divnext."\" style=\"display:none\"><!-- --></div>+:::+".$antispam['text']."-:::-".$antispam['imagehash'];
		exit;
	}

	function parse_row( $row = array() , $sendbutton = array() )
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions, $_USEROPTIONS;
		$poster = array();
		if ($bbuserinfo['id']) {
			$poster = $forums->func->fetch_user( $row );
		} else {
			$poster = $forums->func->set_up_guest( $row['username'] );
			$row['name_css'] = 'unreg';
		}
		if ($row['anonymous']) {
			if ($bbuserinfo['usergroupid'] == 4) {
				$poster['name'] = $poster['name']." (".$forums->lang['anonymouspost'].")";
			} else {
				$poster = array();
				$poster['name'] = $forums->lang['anonymous'].'-'.substr(md5($row['joindate']),0,6);
				$poster['id'] = 0;
				$poster['grouptitle'] = $forums->lang['byanonymous'];
				$poster['posts'] = $forums->lang['unknown'];
			}
		}

		$row['post_css'] = $this->postcount % 2 ? 'row1' : 'row2';
		$row['altrow']   = 'row1';
		if ( $row['userid'] ) {
			$this->user['options'] = intval($this->user['options']);
			foreach($_USEROPTIONS AS $optionname => $optionval) {
				$row["$optionname"] = $row['options'] & $optionval ? 1 : 0;
			}
			$poster['status'] = 1;
		} else {
			$poster['status'] = '';
		}
		$row['delete_button'] = $this->delete_button($row['pid'], $poster, $sendbutton);
		$row['edit_button']   = $this->edit_button($row['pid'], $poster, $row['dateline'], $sendbutton);
		$row['dateline']     = $forums->func->get_date( $row['dateline'], 2 );
		$forums->func->check_cache('icon');
		$row['post_icon'] = $row['iconid'] ? 1 : 0;
		$row['post_icon_hash'] = $forums->cache['icon'][$row['iconid']]['image'];
		$row['host'] = "IP: ".$row['host']." &#0124;";
		$row['report_link'] = (($bboptions['disablereport'] != 1) AND ( $bbuserinfo['id'] )) ? 1 : 0;
		$row['signature'] = "";
		if ($poster['id']) {
			$poster['name'] = "<a href='profile.php?{$forums->sessionurl}u=".$poster['id']."'>".$poster['name']."</a>";
		}
		$this->lib->parser->show_html  = ( $this->lib->forum['allowhtml'] AND $forums->cache['usergroup'][ $poster['usergroupid'] ]['canposthtml'] ) ? 1 : 0;
		$row['pagetext'] = $this->lib->parser->convert_text( $row['pagetext'] );
		$postcount = $DB->query_first( "SELECT count(*) AS total FROM ".TABLE_PREFIX."post WHERE threadid='".$row['threadid']."'");
		$row['postcount'] = $postcount['total'];
		return array( 'row' => $row, 'poster' => $poster );
	}

	function returntext()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		foreach ($_POST['rsargs'] as $row){
			$value[] = $row;
		}
		$value[1] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $value[1]);
		$forum = $forums->forum->single_forum( $value[2] );
		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();
		if ( $value[3] ) {
			$bbuserinfo['usewysiwyg'] = ($value[3] == 'wysiwyg') ? 1 : 0;
		} else {
			$bbuserinfo['usewysiwyg'] = ($bboptions['mxemode']) ? 1 : 0;
		}
		$value[1] = $bbuserinfo['usewysiwyg'] ? $value[1] : $forums->func->htmlspecialchars_uni($value[1]);
		$value[1] = $lib->censoredwords($value[1]);
		$post = $lib->convert(array(
			'text' => $forums->func->stripslashes_uni($value[1]),
			'allowsmilies' => 1,
			'allowcode' => $forum['allowbbcode'],
			'allowhtml' => $forum['allowhtml'],
			'usewysiwyg' => $bbuserinfo['usewysiwyg'])
		);
		if (!$bbuserinfo['canappendedit']) {
			$edittime = $forums->func->get_date( TIMENOW , 2);
			$forums->lang['_editinfo'] = sprintf( $forums->lang['_editinfo'], $bbuserinfo['name'], $edittime );
			$post .= "<!--editpost--><br /><br /><br /><div><font class='editinfo'>".$forums->lang['_editinfo']."</font></div><!--editpost1-->";
		}
		if ($bbuserinfo['is_mod']) {
			$DB->query_first( "UPDATE ".TABLE_PREFIX."post SET pagetext = '".addslashes($post)."' WHERE pid=".intval($value[0])."" );
			$post = $lib->convert_text($post);
			require(ROOT_PATH."includes/functions_showthread.php");
			$show = new functions_showthread();
			$post = preg_replace( "/<!--emule1-->(.+?)<!--emule2-->/ie", "\$show->paste_emule('\\1')", $post );
			echo $value[0]."]:::[".str_replace('%', '%25', $post);
		} else {
			$str = $forums->lang['_ajaxreturntext'];
			echo $value[0]."]:::[".$str;
		}
	}

	function change_name()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate' );
		foreach ($_INPUT['rsargs'] AS $row){
			$value = explode("]:::[", $row);
		}
		$thread = $DB->query_first("SELECT title, forumid FROM ".TABLE_PREFIX."thread WHERE tid = '".intval($value[0])."' ");
		$this->init_mod($thread['forumid']);
		if (!$bbuserinfo['_moderator'][ $thread['forumid'] ] AND !$this->moderator['caneditthreads'] AND !$bbuserinfo['supermod']) {
			return "";
		}
		if (preg_match( '#<strong>(.*)</strong>#siU', $thread['title'] )) {
			$thread['title'] = preg_replace('#<strong>(.*)</strong>#siU', '\\1', $thread['title']);
			$thread['bb'] = 1;
		}
		if (preg_match( '#<font color=(\'|")(.*)(\\1)>(.*)</font>#siU', $thread['title'] )) {
			$thread['color'] = preg_replace('#<font color=(\'|")(.*)(\\1)>(.*)</font>#siU', '\\2', $thread['title']);
		}
		$thread['title'] = strip_tags($thread['title']);
		$value[1] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $value[1]);
		$value[1] = isset($value[1]) ? trim($value[1]) : "";
		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();
		$value[1] = $lib->censoredwords($value[1]);
		$title = $value[1];
		if ($thread['color']) {
			$value[1] = "<font color='".$thread['color']."'>".$value[1]."</font>";
		}
		if ($thread['bb']) {
			$value[1] = "<strong>".$value[1]."</strong>";
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."thread SET title = '".addslashes($value[1])."' WHERE  tid = '".intval($value[0])."';");
		$forums->func->check_cache('forum_stats', 'forum');
		if ($forums->cache['forum_stats'][$thread['forumid']]['lastthreadid'] == intval($value[0])) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."forum SET lastthread='".addslashes(strip_tags($title))."' WHERE id='".$thread['forumid']."'");
			$forums->cache['forum_stats'][$thread['forumid']]['lastthread'] = strip_tags($title);
			$forums->func->update_cache(array('name' => 'forum_stats'));
		}
		require(ROOT_PATH."includes/functions_moderate.php");
		$modlog = new modfunctions();
		$modlog->add_moderate_log('0',$value[0],'0','',$forums->lang['changetitle']);
		echo $value[0]."]:::[".$value[1];
	}

	function closethread()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate' );
		foreach ($_INPUT['rsargs'] AS $row){
			$value[] = $row;
		}
		$this->init_mod($value[1]);
		if ($bbuserinfo['_moderator'][ $value[1] ] OR $bbuserinfo['supermod'] OR $this->moderator['canopenclose']) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."thread SET open = 0 WHERE tid = '".$value[0]."';");
			$postuid = $DB->query_first("SELECT postuserid FROM ".TABLE_PREFIX."thread WHERE tid = '".$value[0]."';");
			require(ROOT_PATH."includes/functions_moderate.php");
			$modlog = new modfunctions();
			$modlog->add_moderate_log('0',$value[0],'0','',$forums->lang['closethread']);
			$message = $forums->lang['closethread'].": <a href='showthread.php?t=".$value[0]."'>".$forums->lang['thisthread']."</a>";
			$title = $forums->lang['closethreadpm'];
			$this->sendpm($message,$title,$postuid['postuserid']);
			echo $value[0]."]:::[".$value[1];
		}
	}

	function openthread(){
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate' );
		foreach ($_INPUT['rsargs'] AS $row){
			$value[] = $row;
		}
		$this->init_mod($value[1]);
		if ($bbuserinfo['_moderator'][ $value[1] ] OR $bbuserinfo['supermod'] OR $this->moderator['canopenclose']) {
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."thread SET open = 1 WHERE tid = '".$value[0]."';");
			$postuid = $DB->query_first("SELECT postuserid FROM ".TABLE_PREFIX."thread  WHERE  tid = '".$value[0]."';");
			require(ROOT_PATH."includes/functions_moderate.php");
			$modlog = new modfunctions();
			$modlog->add_moderate_log('0',$value[0],'0','',$forums->lang['openthread']);
			$message = $forums->lang['openthread'].": <a href='showthread.php?t=".$value[0]."'>".$forums->lang['thisthread']."</a>";
			$title = $forums->lang['openthreadpm'];
			$this->sendpm($message,$title,$postuid['postuserid']);
			echo $value[0]."]:::[".$value[1];
		}
	}

	function checkuser(){
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'register' );
		foreach ($_INPUT['rsargs'] as $value){
			$text = $value;
		}
		$text = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $text);
		$username = preg_replace( "/\s{2,}/", " ", trim( str_replace( '|', '&#124;' , $text ) ) );
		$check = $forums->func->unclean_value($username);
		$len_u = $forums->func->strlen($check);
		if (empty($username) || strstr($check, ';')) {
			echo 'no';
			exit;
		} else if ($len_u < $bboptions['usernameminlength'] OR $len_u > $bboptions['usernamemaxlength'] OR strlen($username) > 60) {
			echo 'length';
			exit;
		}
		$DB->query("SELECT content FROM ".TABLE_PREFIX."banfilter WHERE type = 'name'");
		while($r = $DB->fetch_array()) {
			$banfilter[] = $r['content'];
			if ($r['content']) {
				if (preg_match( "/".preg_quote($r['content'], '/' )."/i", $username)) {
					echo 'no';
					exit;
				}
			}
		}
		$checkuser = $DB->query_first("SELECT id, name, email, usergroupid, password, host, salt
				FROM ".TABLE_PREFIX."user
				WHERE LOWER(name)='".$forums->func->strtolower($username)."' OR name='".$username."'");
		if (($checkuser['id']) OR ($username == $forums->lang['guest'])) {
			echo "exist";
			exit;
		}else{
			echo "ok";
		}
	}

	function checkmail(){
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'register' );
		foreach ($_INPUT['rsargs'] as $value){
			$text = $value;
		}
		$text = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $text);
		$email = $forums->func->strtolower( trim($text) );
		if (strlen($email) < 6) {
			echo "no";
			exit;
		}
		$email = $forums->func->clean_email($email);
		if ( ! $email ) {
			echo "no";
			exit;
		}
		$DB->query("SELECT content FROM ".TABLE_PREFIX."banfilter WHERE type = 'email'");
		while( $r = $DB->fetch_array() ) {
			if ($r['content']) {
				$banemail = preg_replace("/\*/", '.*' , $r['content']);
				if ( preg_match( "/$banemail/", $email ) ) {
					echo "no";
					exit();
				}
			}
		}
		$DB->query("SELECT email FROM ".TABLE_PREFIX."user WHERE email = '".$email."'");
		if ( $DB->num_rows() != 0 ) {
			echo "exist";
			exit;
		}else{
			echo "ok";
		}
	}

	function delete_button($postid, $poster, $sendbutton)
	{
		global $forums, $bbuserinfo, $bboptions, $_INPUT;
		if (!$bbuserinfo['id']) return "";
		if ( $bboptions['isajax']) {
			$button = 'isajax';
		} else {
			$button = 'noajax';
		}
		if ($bbuserinfo['supermod'] OR $this->moderator['candeleteposts'] OR ($poster['id'] == $bbuserinfo['id'] AND ($bbuserinfo['candeletepost']))) {
			return $button;
		}
		return "";
	}

	function edit_button($postid, $poster, $dateline, $sendbutton)
	{
		global $forums, $bbuserinfo, $_INPUT;
		if (!$bbuserinfo['id']) return "";
		$button = 1;
		if ($bbuserinfo['supermod'] OR $this->moderator['caneditposts']) {
			return $button;
		}
		if ($poster['id'] == $bbuserinfo['id'] AND ($bbuserinfo['caneditpost'])) {
			if ($bbuserinfo['edittimecut'] > 0) {
				if ( $dateline > ( TIMENOW - ( intval($bbuserinfo['edittimecut']) * 60 ) ) ) {
					return $button;
				} else {
					return "";
				}
			} else {
				return $button;
			}
		}
		return "";
	}

	function sendpm($message,$title,$to_user){
		global $forums, $DB, $_INPUT, $bbuserinfo, $bboptions;
		$DB->query_unbuffered( "INSERT INTO ".TABLE_PREFIX."pmtext
							(dateline, message, deletedcount, savedcount)
						VALUES
							(".TIMENOW.", '".addslashes($message)."', 0, 1)"
							);
							$pmtextid = $DB->insert_id();
							$DB->query_unbuffered( "INSERT INTO ".TABLE_PREFIX."pm
								(messageid, dateline, title, fromuserid, touserid, folderid, userid)
							VALUES
								(".$pmtextid.", ".TIMENOW.", '".addslashes($title)."', ".$bbuserinfo['id'].", ".$to_user.", 0, ".$to_user.")"
								);
								$this->rebuild_foldercount( $to_user, "", '0', '-1', 'save', ",pmtotal=pmtotal+1, pmunread=pmunread+1" );
	}

	function rebuild_foldercount($userid, $folders, $curfolderid, $pmcount, $nosave='save', $extra="")
	{
		global $DB, $forums;
		$rebuild = array();
		if ( ! $folders ) {
			$user = $DB->query_first( "SELECT pmfolders FROM ".TABLE_PREFIX."user WHERE id=".$userid."" );
			$def_folders = array('0' => array( 'pmcount' => 0, 'foldername' => $forums->lang['_inbox'] ),
			'-1' => array( 'pmcount' => 0, 'foldername' => $forums->lang['_outbox'] ),
			);
			$folders = $user['pmfolders'] ? unserialize($user['pmfolders']) : $def_folders;
		}
		foreach( $folders AS $id => $data ) {
			if ( $id == $curfolderid ) {
				$data['pmcount'] = ($pmcount == '-1') ? intval($data['pmcount'] + 1) : intval($pmcount);
			}
			$rebuild[$id] = $data;
		}
		$pmfolders = addslashes(serialize($rebuild));
		if ( $nosave != 'nosave' ) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."user SET pmfolders='".$pmfolders."' ".$extra." WHERE id=".$userid."" );
		}
		return $pmfolders;
	}

	function cbsend()
	{
		global $DB, $forums, $bbuserinfo, $bboptions, $_INPUT;
		$_POST['rsargs']['0'] = intval($_POST['rsargs']['0']);
		$thread = $DB->query_first("SELECT title, forumid, sticky FROM ".TABLE_PREFIX."thread WHERE tid = '".$_POST['rsargs']['0']."' ");
		$this->init_mod($thread['forumid']);
		if (!$bbuserinfo['_moderator'][ $thread['forumid'] ] AND !$this->moderator['caneditthreads'] AND !$bbuserinfo['supermod']) {
			return "";
		}
		if (preg_match( '#<strong>(.*)</strong>#siU', $thread['title'] )) {
			$thread['title'] = preg_replace('#<strong>(.*)</strong>#siU', '\\1', $thread['title']);
			$thread['bb'] = 1;
		}
		if (preg_match( '#<font color=(\'|")(.*)>(.*)</font>#siU', $thread['title'] )) {
			$thread['color'] = preg_replace('#<font color=(\'|")(.*)>(.*)</font>#siU', '\\2', $thread['title']);
		}
		$thread['title'] = strip_tags($thread['title']);

		if ($_POST['rsargs']['1'] && $_POST['rsargs']['1'] != 'X') {
			$thread['color'] = $_POST['rsargs']['1'];
		}
		if ($_POST['rsargs']['2']) {
			$thread['bb'] = 1;
		} else {
			$thread['bb'] = 0;
		}
		if ($thread['color'] && $thread['color'] != 'reset') {
			$thread['title'] = "<font color='".$thread['color']."'>".$thread['title']."</font>";
		}
		if ($thread['bb']) {
			$thread['title'] = "<strong>".$thread['title']."</strong>";
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."thread SET title = '".addslashes($thread['title'])."' WHERE  tid = '".$_POST['rsargs']['0']."';");
		if ($thread['sticky'] == 2) {
			$forums->func->recache_cache('globalstick');
		}
		echo $_POST['rsargs']['0']."]:::[".$thread['title'];
	}

	function sendattach(){
		global $DB, $forums, $bbuserinfo, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		require(ROOT_PATH."includes/functions_post.php");
		$lib = new functions_post(0);
		if ($_POST['rsargs']['1'] == 'NULL'){
			$upload = $lib->fetch_upload_form($_POST['rsargs']['0'],'new');
		} else {
			$upload = $lib->fetch_upload_form($_POST['rsargs']['0'],'edit',$_POST['rsargs']['1']);
		}
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		print_r($upload['tmp']);
		exit;
	}

	function removeattach(){
		global $DB, $forums, $bbuserinfo, $_INPUT;
		require(ROOT_PATH."includes/functions_post.php");
		$lib = new functions_post(0);
		$lib->remove_attachment( intval($_POST['rsargs']['0']), $_POST['rsargs']['1'] );
		if ($_POST['rsargs']['2'] == 'NULL'){
			$upload = $lib->fetch_upload_form($_POST['rsargs']['1'],'new');
		} else {
			$upload = $lib->fetch_upload_form($_POST['rsargs']['1'],'edit',$_POST['rsargs']['2']);
		}
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		print_r($upload['tmp']);
		exit;
	}

	function returnpagetext()
	{
		global $DB, $forums, $bbuserinfo, $_INPUT, $bboptions;
		foreach ($_POST['rsargs'] as $row){
			$pid = intval($row);
		}
		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();
		$post = $DB->query_first("SELECT p.pagetext, p.threadid, p.allowsmile, t.forumid FROM ".TABLE_PREFIX."post p LEFT JOIN ".TABLE_PREFIX."thread t ON (p.threadid = t.tid) WHERE pid = ".$pid."");
		$forum = $forums->forum->single_forum( $post['forumid'] );
		$post['pagetext'] = preg_replace("#<!--editpost-->(.+?)<!--editpost1-->#","",$post['pagetext']);
		$post['pagetext'] = $lib->unconvert($forums->func->stripslashes_uni($post['pagetext']), $forum['allowbbcode'], $forum['allowhtml'], 1, 1);
		echo $pid."]:::[" . str_replace('%', '%25', $post['pagetext']);
		exit;
	}

	function deletepost()
	{
		global $DB, $forums, $bbuserinfo, $_INPUT, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'moderate' );
		$pid = intval($_INPUT['rsargs'][0]);
		$fid = intval($_INPUT['rsargs'][2]);
		if ($bbuserinfo['_moderator'][$fid]) {
			$this->moderator = $bbuserinfo['_moderator'][$fid];
		}
		$post = $DB->query_first('SELECT p.userid, p.username, p.threadid, p.dateline, t.title, t.forumid, p.threadid
			FROM ' . TABLE_PREFIX . 'post p
				LEFT JOIN ' . TABLE_PREFIX . "thread t
					ON p.threadid = t.tid
			WHERE p.pid = $pid");
		$passed = (($bbuserinfo['supermod'] OR $this->moderator['candeleteposts'] OR ($bbuserinfo['candeletepost'] AND $bbuserinfo['id'] == $post['userid'])) && $fid == $post['forumid']) ? true : false;
		if (!$passed) {
			echo $passed.']:::[';
			exit;
		}
		include_once(ROOT_PATH . 'includes/functions_moderate.php');
		$modfunc = new modfunctions();
		$recycleforum = 0;
		if ($bboptions['enablerecyclebin'] AND $bboptions['recycleforumid']) {
			if ($forums->cache['forum'][$bboptions['recycleforumid']]['allowposting']) {
				if ($bbuserinfo['cancontrolpanel']) {
					$recycleforum = $bboptions['recycleforadmin'] ? $bboptions['recycleforumid'] : 0;
				} else if ($bbuserinfo['supermod']) {
					$recycleforum = $bboptions['recycleforsuper'] ? $bboptions['recycleforumid'] : 0;
				} else if ($bbuserinfo['is_mod']) {
					$recycleforum = $bboptions['recycleformod'] ? $bboptions['recycleforumid'] : 0;
				} else {
					$recycleforum = $bboptions['recycleforumid'];
				}
			}
		}
		$forumsql = $recycleforumsql = array();
		if ($recycleforum && $recycleforum != $fid) {
			$ptitle = array (
				'title' => $forums->lang['deletepost'] . ':' . strip_tags($post['title']),
				'dateline' => $post['dateline'],
				'postusername' => $post['username'],
				'forumid' => $recycleforum,
				'visible' => 1,
				'postuserid' => $post['userid'],
				'open' => 1,
				'post' => 0,
				'postusername' => $post['username'],
				'lastposterid' => $post['userid'],
				'lastposter' => $post['username'],
				'firstpostid' => $pid,
				'lastpostid' => $pid,
				'lastpost' => TIMENOW,
				'iconid' => 0,
				'pollstate' => 0,
				'lastvote' => 0,
				'views' => 0,
				'sticky' => 0,
			);
			$forums->func->fetch_query_sql($ptitle, 'thread');
			$threadid = $DB->insert_id();
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."post SET threadid = '".$threadid."', newthread = 1 WHERE pid = $pid");
			$modfunc->rebuild_thread($post['threadid'], 0);
			if ($forums->forum->foruminfo[$fid]['lastthreadid'] == $post['threadid']) {
				$thread = $DB->query_first("SELECT title, tid, lastpost, lastposterid, lastposter FROM ".TABLE_PREFIX."thread WHERE forumid=$fid AND visible=1 ORDER BY lastpost DESC LIMIT 0, 1");
				$dbs = array (
					'lastthread' => $thread['title'] ? strip_tags($thread['title']) : '',
					'lastthreadid' => $thread['tid'] ? $thread['tid'] : '',
					'lastpost' => $thread['lastpost'] ? $thread['lastpost'] : '',
					'lastposter' => $thread['lastposter'] ? $thread['lastposter'] : '',
					'lastposterid' => $thread['lastposterid'] ? $thread['lastposterid'] : '',
				);
				$forums->func->fetch_query_sql($dbs, 'forum', "id=$fid");
				foreach ($dbs as $k => $v) {
					$forums->cache['forum_stats'][$fid][$k] = $v;
				}
				unset($dbs);
			}
			$recycleforumsql[] = 'thread = thread + 1';
			$recycleforumsql[] = 'post = post + 1';
			$forums->cache['forum_stats'][$recycleforum]['thread']++;
			$forums->cache['forum_stats'][$recycleforum]['post']++;
			$dbs = array(
				'lastthread' => strip_tags($ptitle['title']),
				'lastthreadid' => $threadid,
				'lastpost' => $ptitle['lastpost'],
				'lastposter' => $ptitle['lastposter'],
				'lastposterid' => $ptitle['lastposterid'],
			);
			foreach ($dbs as $key => $val) {
				$recycleforumsql[] = $key . ' = \'' . addslashes($val) . '\'';
				$forums->cache['forum_stats'][$recycleforum][$key] = $val;
			}
			unset($dbs);
		} else {
			$modfunc->post_delete($pid);
		}
		$forumsql[] = 'post = post - 1';
		$forums->cache['forum_stats'][$fid]['post']--;
		$today = getdate();
		$posttime = $forums->func->get_time($post['dateline'], 'd');
		if ($today['mday'] == $posttime) {
			$forumsql[] = 'todaypost = todaypost - 1';
			$forums->cache['forum_stats'][$fid]['todaypost']--;
			if ($recycleforum AND $recycleforum != $fid) {
				$recycleforumsql[] = 'todaypost = todaypost + 1';
				$forums->cache['forum_stats'][$recycleforum]['todaypost']++;
			}
		}
		$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."forum SET ".implode(',', $forumsql)." WHERE id = $fid");
		if ($recycleforum && $recycleforum != $fid && $recycleforumsql) {
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."forum SET ".implode(',', $recycleforumsql)." WHERE id = $recycleforum");
		}
		$forums->func->update_cache(array('name' => 'forum_stats'));

		if ($_INPUT['rsargs'][1]) {
			$_INPUT['rsargs'][1] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $_INPUT['rsargs'][1]);
			$deltitle = "<a href='showthread.php?t=".$post['threadid']."'  target='_blank'>".$post['title']."</a>";
			$forums->lang['yourpostdeletedinfos'] = sprintf($forums->lang['yourpostdeletedinfo'], $deltitle, $_INPUT['rsargs']['1']);
			$this->sendpm($forums->lang['yourpostdeletedinfos'], $forums->lang['yourpostdeleted'], $post['userid']);
		}
		echo $passed."]:::[".$_POST['rsargs'][0];
		exit;
	}

	function init_mod($forumid = 0) {
		global $bbuserinfo;
		if ( $bbuserinfo['id'] AND !$bbuserinfo['supermod'] ) {
			$this->moderator = $bbuserinfo['_moderator'][$forumid];
		}
	}

	function check_permission( $thread=array() )
	{
		global $forums, $DB, $bbuserinfo;
		if ($thread['pollstate'] == 2 AND !$bbuserinfo['supermod']) {
			return false;
		}
		if ($thread['postuserid'] == $bbuserinfo['id']) {
			if (!$bbuserinfo['canreplyown']) {
				return false;
			}
		}
		$usercanreplay = $forums->func->fetch_permissions($this->lib->forum['canreply'], 'canreply');
		if ($thread['postuserid'] != $bbuserinfo['id'] AND $usercanreplay == FALSE ) {
			if (!$bbuserinfo['canreplyothers']) {
				return false;
			}
		}
		if ( $usercanreplay == FALSE ) {
			return false;
		}
		if (!$thread['open']) {
			if (!$bbuserinfo['canpostclosed']) {
				return false;
			}
		}
		return true;
	}

	function changerule(){
		global $forums, $DB, $bbuserinfo, $_INPUT;
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		foreach ($_INPUT['rsargs'] as $row){
			$info[] = $row;
		}
		if ( $bbuserinfo['_moderator'][ $info['1'] ] ) {
			$this->moderator = $bbuserinfo['_moderator'][ $info['1'] ];
		}
		$forumrule = trim($info['0']) ? 1 : 0;
		if (!$forumrule) {
			echo $info['1']."-:::-".$forums->lang['contentiszero'];
			exit;
		}
		$passed = ($this->moderator['caneditrule'] OR $bbuserinfo['supermod']) ? TRUE : FALSE;
		if ($passed) {
			if ( is_writeable(ROOT_PATH . 'cache/cache' ) ) {
				if ( file_exists(ROOT_PATH . "cache/cache/rule_{$info['1']}.txt" ) ) {
					if ( ! is_writeable(ROOT_PATH . "cache/cache/rule_{$info['1']}.txt" ) ) {
						echo $info['1']."-:::-".$forums->lang['cachefoldererror'];
						exit;
					}
				}
			} else {
				echo $info['1']."-:::-".$forums->lang['cachefoldererror'];
				exit;
			}
			if ($fp = @fopen("cache/cache/rule_{$info['1']}.txt", 'wb')) {
				require("includes/functions_codeparse.php");
				$lib = new functions_codeparse();
				$_POST['rsargs']['0'] = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $_POST['rsargs']['0']);
				$_POST['rsargs']['0'] = $lib->censoredwords($_POST['rsargs']['0']);
				$content = $lib->convert( array( 'text' => $forums->func->stripslashes_uni($_POST['rsargs']['0']),
				'allowsmilies' => 1,
				'allowcode' => 1,
				'allowhtml' => 1
				));
				@fwrite($fp, $content);
				@fclose($fp);
				@chmod( "cache/cache/rule_{$info['1']}.txt", 0777 );
			}
			$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."forum SET forumrule = '".$forumrule."' WHERE id = '".$info['1']."' ");
			echo $info['1']."]:::[".$content;
		}
	}

	function change_cash(){
		global $forums, $DB, $bbuserinfo, $_INPUT, $bboptions;
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$forums->lang = $forums->func->load_lang($forums->lang, 'showthread' );
		foreach ($_INPUT['rsargs'] as $row){
			$info[] = $row;
		}
		$info['0'] = intval($info['0']);
		$info['1'] = intval($info['1']);
		$info['2'] = intval($info['2']);
		$info['3'] = intval($info['3']);
		if ($info['0'] == $bbuserinfo['id']) {
			echo $info['0']."-:::-".$info['1']."]:::[".$forums->lang['modrep_sameuserid_error'];
			exit;
		}
		$forums->func->check_cache('banksettings');
		if ($bbuserinfo['_moderator'][$info['4']]['forumid'] == $info['4'] OR $bbuserinfo['supermod']){
			if ( abs($info['2']) > $bboptions['modrepmax'] ) {
				echo $info['0']."-:::-".$info['1']."]:::[".$forums->lang['modrep_limited_error'];
				exit;
			}
			$cashpost = $DB->query_first("SELECT cashpost FROM ".TABLE_PREFIX."post WHERE pid = '".$info['1']."'");
			$oldrecord = unserialize($cashpost['cashpost']);
			if ($info['2']) {
				if ($cashpost['cashpost']) {
					echo $info['0']."-:::-".$info['1']."]:::[".sprintf($forums->lang['alreadycashed'],$oldrecord['user']);
					exit;
				}
				$cashpostrecord['user'] = $bbuserinfo['name'];
				$cashpostrecord['cashnum'] = $info['2'];
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."post SET cashpost = '".serialize($cashpostrecord)."' WHERE pid = ".$info['1']."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allcash = allcash+".$info['2']." WHERE tid = ".$info['3']."");
			} else {
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."post SET cashpost = '' WHERE pid = ".$info['1']."");
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."user SET cash = cash - ".intval($oldrecord['cashnum'])." WHERE id = ".$info['0']."");
				$DB->shutdown_query("UPDATE ".TABLE_PREFIX."thread SET allcash = allcash-".intval($oldrecord['cashnum'])." WHERE tid = ".$info['3']."");
				echo $info['0']."-:::-".$info['1']."]:::[".sprintf($forums->lang['cashcleanupsuc'],$forums->cache['banksettings']['bankcurrency']);
				exit;
			}
			$DB->shutdown_query("UPDATE ".TABLE_PREFIX."user SET cash = cash + ".$info['2']." WHERE id = ".$info['0']."");
			$illuminate = "<a href='".$bboptions['bburl']."/showthread.php?t=".$info['3']."'>".$forums->lang['banklog_thread']."</a>";
			$DB->shutdown_query("INSERT INTO ".TABLE_PREFIX."banklog (fromuserid, touserid, action, dateline) VALUES (".$bbuserinfo['id'].", ".$info['0'].", '".addslashes($illuminate)."', ".TIMENOW.")");
			echo $info['0']."-:::-".$info['1']."]:::[";
			printf($forums->lang['send_cash_success'],$forums->cache['banksettings']['bankcurrency'],$info['2']);
			exit;
		} else {
			echo $info['0']."-:::-".$info['1']."]:::[".$forums->lang['noperms'];
			exit;
		}
	}

	function sendpreview(){
		global $bboptions, $bbuserinfo, $forums, $_INPUT;
		require(ROOT_PATH."includes/functions_codeparse.php");
		$lib = new functions_codeparse();
		$fid = intval($_INPUT['rsargs'][1]);
		$allowsmilies = intval($_INPUT['rsargs'][2]);
		$content = $_POST['rsargs'][0];
		$content = preg_replace('#%u([0-9A-F]{1,4})#ie', "\$forums->func->int_utf8(hexdec('\\1'))", $content);
		$cookie_mxeditor = $forums->func->get_cookie('mxeditor');
		if ( $cookie_mxeditor ) {
			$bbuserinfo['usewysiwyg'] = ($cookie_mxeditor == 'wysiwyg') ? 1 : 0;
		} elseif ($bboptions['mxemode']) {
			$bbuserinfo['usewysiwyg'] = 1;
		} else {
			$bbuserinfo['usewysiwyg'] = 0;
		}
		$content = $forums->func->stripslashes_uni($content);
		$content = $bbuserinfo['usewysiwyg'] ? $content : $forums->func->htmlspecialchars_uni($content);
		$content = $lib->censoredwords($content);
		if ($fid > 0) {
			$thisforum = $forums->forum->single_forum($fid);
			$allowcode = intval($thisforum['allowbbcode']);
			$allowhtml = intval($thisforum['allowhtml']);
		} else if ($fid === 0) {
			$allowcode = intval($bboptions['pmallowbbcode']);
			$allowhtml = intval($bboptions['pmallowhtml']);
		} else {
			$allowcode = 0;
			$allowhtml = 0;
		}
		$content = $lib->convert( array( 'text' => $content,
			'allowsmilies' => $allowsmilies,
			'allowcode' => $allowcode,
			'allowhtml' => $allowhtml,
			'usewysiwyg' => $bbuserinfo['usewysiwyg']
		) );
		echo $content;
	}

	function smilespage($n)
	{
		global $forums, $bboptions;
		$forums->func->check_cache('smile');
		$smile_count = count($forums->cache['smile']);
		if ($smile_count == 0) {
			echo 'none';
			return;
		}
		$all_smiles = $bboptions['rowsmiles'] * $bboptions['perlinesmiles'];
		if ($all_smiles == 0) {
			echo 'none';
			return;
		}
		$lastpage = floor($smile_count/$all_smiles);
		if ($n[0] == 0 && $n[1] == 0) {
			$n[0] = $lastpage - 1;
			$n[1] = 1;
		} else if ($n[0] == $lastpage && $n[1] == 1) {
			$n[0] = 1;
			$n[1] = 0;
		}
		if ($n[1] == 0) {
			$page = --$n[0];
		} else if ($n[1] == 1) {
			$page = ++$n[0];
		}
		for ($i = $page * $all_smiles, $x = $i + $all_smiles; $i < $x; $i++) {
			if (isset($forums->cache['smile'][$i])) {
				$smiles[$forums->cache['smile'][$i]['id']] = $forums->cache['smile'][$i];
			} else {
				break;
			}
		}
		include $forums->func->load_template('newpost_smiles');
		echo '-:::-'.$page;
		exit();
	}
}

$output = new mxajax();
$output->show();
?>
