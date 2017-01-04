<?php
!function_exists('readover') && exit('Forbidden');

##主题分类
$t_typedb=array();
$t_per=0;$t_exits=0;
$t_db=$foruminfo['t_type'];
if($t_db){
	$t_typedb=explode("\t",$t_db);
	$t_typedb = array_unique ($t_typedb);
	$t_per=$t_typedb[0];unset($t_typedb[0]);
}

if($foruminfo['allowpost'] && strpos($foruminfo['allowpost'],','.$groupid.',')===false && !$admincheck){
	Showmsg('postnew_forum_right');
}
if(!$foruminfo['allowpost'] && !$admincheck && $gp_allownewvote==0 && $action=='vote'){
	Showmsg('postnew_group_vote');
}
if(!$foruminfo['allowpost'] && !$admincheck && $gp_allowpost==0){
	Showmsg('postnew_group_right');
}

if (!$_POST['step']){
	foreach($t_typedb as $value){
		if($value) $t_exits=1;
	}

	require_once(R_P.'require/header.php');
	$guidename=forumindex($foruminfo['fup']);
	$msg_guide=headguide($guidename);	
	if($winddb['payemail']){
		list(,$payemail) = explode("\t",$winddb['payemail']);
		$winddb['email']=$payemail;
	}
	require_once PrintEot('post');footer();
}elseif($_POST['step']==2){
	##主题分类
	if(!$p_type || empty($t_typedb[$p_type]) || ($t_per==0 && !$admincheck)){
		$w_type=0;
	}else{
		$w_type=$p_type;
	}
	
	list($atc_title,$atc_content)=check_data($action);
	require_once(R_P.'require/postupload.php');
	if($action=="vote"){
		$votearray = array();
		$vt_select = Char_cv($vt_select);
		$vt_select = explode("\n",$vt_select);
		foreach($vt_select as $voteoption){
			$voteoption = trim($voteoption);
			if($voteoption){
				$votearray['options'][] = array($voteoption,0,array());
			}
		}
		if(count($vt_select) > $db_selcount){
			Showmsg('vote_num_limit');
		}
		if($mostvotes && is_numeric($mostvotes)){
			$mostvotes>count($vt_select)?$mostvotes=count($vt_select):'';
		} else{
			$mostvotes=count($vt_select);
		}
		$votearray['multiple'] = array($multiplevote,$mostvotes);
		$voteopts = addslashes(serialize($votearray));
		$db->update("INSERT INTO pw_polls(voteopts) VALUES ('$voteopts')");
		$pollid=$db->insert_id();
	} else{
		$pollid=0;
	}
	if($sale && $forumset['allowsale']!=2 && $seller && $subject && $price){
		$seller       = Char_cv($seller);
		$subject      = Char_cv($subject);
		$contact      = Char_cv($contact);
		$demo         = Char_cv($demo);
		$price        = (int)$price;
		$ordinary_fee = (int)$ordinary_fee;
		$express_fee  = (int)$express_fee;
		if(!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$seller)){
			Showmsg('seller_error');
		}
		$atc_content  = "[payto]
(seller)$seller(/seller)
(subject)$subject(/subject)
(body)$atc_content(/body)
(price)$price(/price)
(ordinary_fee)$ordinary_fee(/ordinary_fee)
(express_fee)$express_fee(/express_fee)
(contact)$contact(/contact)
(demo)$demo(/demo)
(method)$method(/method)
[/payto]";

	}
	if ($_POST['atc_convert']=="1"){
		$_POST['atc_autourl'] && $atc_content=autourl($atc_content);
		$atc_content = html_check($atc_content);

		/*
		* [post]、[hide、[sell=位置不能换
		*/
		if(!$foruminfo['allowhide'] || !$gp_allowhidden){
			$atc_content=str_replace("[post]","[\tpost]",$atc_content);
		} elseif($atc_hide=='1'){
			$atc_content="[post]".$atc_content."[/post]";
		}
		if(!$foruminfo['allowencode'] || !$gp_allowencode){
			$atc_content=str_replace("[hide=","[\thide=",$atc_content);
		} elseif($atc_requirervrc=='1' ){
			$atc_content="[hide=".$atc_rvrc."]".$atc_content."[/hide]";
		}
		if(!$foruminfo['allowsell'] || !$gp_allowsell){
			$atc_content=str_replace("[sell=","[\tsell=",$atc_content);
		} elseif($atc_requiresell=='1'){
			$atc_content="[sell=".$atc_money."]".$atc_content."[/sell]";
		}
		if(!$SYSTEM['typeadmin']){
			$digest=0;
		}
		/**
		* 主要因为convert函数需要$tpc_author变量
		*/
		$tpc_author=$windid;
		$lxcontent=convert($atc_content,$db_windpost);
		$ifconvert=$lxcontent==$atc_content ? 1:2;
	}else{
		$ifconvert=1;
	}
	if (($foruminfo['f_check'] == 1 || $foruminfo['f_check'] == 3) && $_G['atccheck'] && !$admincheck){
		$ifcheck = 0;
	} else {
		$ifcheck = 1;
	}
	$atc_iconid=(int)$atc_iconid;
	$db->update("INSERT INTO pw_threads (fid,icon,author,authorid,subject,ifcheck,type,postdate,lastpost,lastposter,hits,replies,topped,digest,pollid,ifupload) VALUES ('$fid','$atc_iconid','".addslashes($windid)."','$winddb[uid]','$atc_title','$ifcheck','$w_type','$timestamp','$timestamp','".addslashes($windid)."','1','0','0','".(int)$digest."','$pollid','$ifupload')");
	$tid = $db->insert_id();
	$db->update("INSERT INTO pw_tmsgs (tid,aid,userip,ifsign,buy,ipfrom,ifconvert,content) VALUES('$tid','$attachs','$onlineip','$atc_usesign', '', '$ipfrom', '$ifconvert','$atc_content')");
	if($aids){
		$db->update("UPDATE pw_attachs SET tid='$tid' WHERE aid IN($aids)");
	}
	if($foruminfo['cms']){
		include_once(R_P.'require/c_search.php');
		insert_key($tid,$keyword);
	}
	$top_post=1;
	$t_date=$timestamp;//主题发表时间 bbspostguide 中用到
	bbspostguide();

	unset($j_p);
	if ($ifcheck==1){
		if ($foruminfo['allowhtm']){
			if($foruminfo['cms']){
				require_once R_P.'require/c_buildhtml.php';
				BuildTopicHtml($tid,$foruminfo);
			} else {
				include_once R_P.'require/template.php';
			}
		}
		lastinfo($fid,$foruminfo['allowhtm'],'new',$foruminfo['cms'].'B');
	}
	if($db_kijiji && $kjj){
		require_once(R_P."require/kijiji.php");
		$strPostXML = init_data($k_email,$area_id,$category_id,$atc_title,$atc_content);
		$response1  = fsockPost($sendtourl,$strPostXML);
	}
	if ($modify){
		ObHeader("post.php?action=modify&fid=$fid&tid=$tid&pid=tpc&article=0");
	} else {
		if(empty($j_p) || $foruminfo['cms']) $j_p="read.php?tid=$tid";
		refreshto($j_p,'enter_thread');
	}
}
?>