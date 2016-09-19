<?php
!function_exists('readover') && exit('Forbidden');
function convert($message,$allow,$type="post") 
{
	global $attachper, $code_num,$code_htm,$phpcode_htm,$foruminfo,$picpath,$imgpath,$stylepath,$attachname,$attachpath,$admincheck,$tpc_author,$tpc_buy,$i_table,$db_cvtimes,$forumset;
	if (file_exists(D_P."data/bbscache/wordsfb.php")){
		include(D_P."data/bbscache/wordsfb.php");
		$replace = array_merge($wordsfb,$replace);
		if($replace){
			foreach($replace as $key => $value){
				$message = str_replace($key,$value,$message);
			}
		}
	}
	$code_num=0;
	$code_htm=array();
	if(strpos($message,"[code]") !== false && strpos($message,"[/code]") !== false){
		$message=preg_replace("/\[code\](.+?)\[\/code\]/eis","phpcode('\\1')",$message,$db_cvtimes);
	}
	if(strpos($message,"[payto]") !== false && strpos($message,"[/payto]") !== false){
		require_once(R_P.'require/paytofunc.php');
		$message=preg_replace("/\[payto\](.+?)\[\/payto\]/eis","payto('\\1')",$message);
	}
	if(strpos($message,"[download]") !== false && strpos($message,"[/download]") !== false){
		require_once(R_P.'require/downloadfunc.php');
		$message=preg_replace("/\[download\](.+?)\[\/download\]/eis","download('\\1')",$message);
	}

	$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is', "<ol type=\\1>\\2</ol>", $message);
	$message = str_replace("[u]","<u>",$message);
	$message = str_replace("[/u]","</u>",$message);
	$message = str_replace("[b]","<b>",$message);
	$message = str_replace("[/b]","</b>",$message);
	$message = str_replace("[i]","<i>",$message);
	$message = str_replace("[/i]","</i>",$message);
	$message = str_replace("[list]","<ul>",$message);
	$message = str_replace('[*]', '<li>', $message);
	$message = str_replace("[/list]","</ul>",$message);
	$message = str_replace("p_w_upload",$attachname,$message);//此处位置不可调换
	$message = str_replace("p_w_picpath",$picpath,$message);//此处位置不可调换

	$searcharray = array(
		"/\[font=([^\[]*)\](.+?)\[\/font\]/is",
		"/\[color=([#0-9a-z]{1,10})\](.+?)\[\/color\]/is",
		"/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is",
	    "/\[email\]([^\[]*)\[\/email\]/is",
		"/\[size=(\d+)\](.+?)\[\/size\]/eis",
		"/(\[align=)(left|center|right)(\])(.+?)(\[\/align\])/is",
		"/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is"
	);
	$replacearray = array(
		"<font face='\\1'>\\2</font>",
		"<font color='\\1'>\\2</font>",
		"<a href='mailto:\\1'>\\2</a>",
		"<a href='mailto:\\1'>\\1</a>",
		"size('\\1','\\2','$allow[size]')",
		"<DIV Align=\\2>\\4</DIV>",
		"<span style='WIDTH:\\1;filter:glow(color=\\2,strength=\\3)'>\\4</span>"
	);
	$message=preg_replace($searcharray,$replacearray,$message);

	if ($allow['pic']){
		$message = preg_replace("/\[img\](.+?)\[\/img\]/eis","cvpic('\\1','','$allow[picwidth]','$allow[picheight]')",$message,$db_cvtimes);
    } else{
		$message = preg_replace("/\[img\](.+?)\[\/img\]/eis","nopic('\\1')",$message,$db_cvtimes);
	}

	if(strpos($message,'[/URL]')!==false || strpos($message,'[/url]')!==false){
		$searcharray = array(
			"/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp)([^\[]*)\](.+?)\[\/url\]/eis",			
			"/\[url\]www\.([^\[]*)\[\/url\]/eis",
			"/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp)([^\[]*)\[\/url\]/eis"
		);
		$replacearray = array(
			"cvurl('\\1','\\2','\\3')",
			"cvurl('\\1')",
			"cvurl('\\1','\\2')",
		);
		$message=preg_replace($searcharray,$replacearray,$message);
	}

	$searcharray = array(
		"/\[fly\]([^\[]*)\[\/fly\]/is",
		"/\[move\]([^\[]*)\[\/move\]/is",
	);
	$replacearray = array(
		"<marquee width=90% behavior=alternate scrollamount=3>\\1</marquee>",
		"<marquee scrollamount=3>\\1</marquee>",
	);
	$message=preg_replace($searcharray,$replacearray,$message);


	if($type=="post"){
		if(strpos($message,'[p:')!==false || strpos($message,'[s:')!==false){
			global $face,$motion,$act;
			include_once(D_P.'data/bbscache/postcache.php');
			$message=preg_replace("/\[s:(.+?)\]/eis","postcache('\\1','1')",$message,$db_cvtimes);

			$act="<font color=red><b>[$tpc_author]</b></font>";
			$message=preg_replace("/\[p:(.+?)\]/eis","postcache('\\1','2')",$message,$db_cvtimes);
		}

		if($foruminfo['allowhide'] && strpos($message,"[post]") !== false && strpos($message,"[/post]") !== false){
			$message=preg_replace("/\[post\](.+?)\[\/post\]/eis","post('\\1')",$message);
		}
		if($foruminfo['allowencode'] && strpos($message,"[hide") !== false && strpos($message,"[/hide]") !== false){
			$message=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/eis","hiden('\\1','\\2')",$message);
		}
		if($foruminfo['allowsell'] && strpos($message,"[sell") !== false && strpos($message,"[/sell]") !== false){
			$message=preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/eis","sell('\\1','\\2')",$message);
		}
	}

	if ($allow['flash']){
        $message = preg_replace("/(\[flash=)(\d+?)(\,)(\d+?)(\])(.+?)(\[\/flash\])/is","<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH=\\2 HEIGHT=\\4><PARAM NAME=MOVIE VALUE=\\6><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=\\6 WIDTH=\\2 HEIGHT=\\4 PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT><br />[<a target=_blank href=\\6>Full Screen</a>] ",$message,$db_cvtimes);
	}else{
		$message = preg_replace("/(\[flash=)(\d+?)(\,)(\d+?)(\])(.+?)(\[\/flash\])/is","<img src='$imgpath/$stylepath/file/music.gif' align='absbottom'> <a target=_blank href=\\6>flash: \\6</a>",$message,$db_cvtimes);
	}

	if (strpos($message,"[quote]") !== false && strpos($message,"[/quote]") !== false){
		$message=preg_replace("/\[quote\](.+?)\[\/quote\]/eis","qoute('\\1')",$message);
	}
	if(is_array($code_htm)){
		krsort($code_htm);
		foreach($code_htm as $codehtm){
			foreach($codehtm as $key=>$value){
				$message=str_replace("<\twind_code_$key\t>",$value,$message);
			}
		}
	}
	if($type=="post"){
		if($allow['mpeg']){
			global $lang;
			require_once GetLang('bbscode');
			$message = preg_replace("/\[wmv\](.+?)\[\/wmv\]/is","<EMBED src=\\1 HEIGHT=\"256\" WIDTH=\"314\" AutoStart=1></EMBED>",$message,$db_cvtimes);
			$message = preg_replace("/\[rm\](.+?)\[\/rm\]/is","<object classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA height=241 id=Player width=316 VIEWASTEXT><param name=\"_ExtentX\" value=\"12726\"><param name=\"_ExtentY\" value=\"8520\"><param name=\"AUTOSTART\" value=\"0\"><param name=\"SHUFFLE\" value=\"0\"><param name=\"PREFETCH\" value=\"0\"><param name=\"NOLABELS\" value=\"0\"><param name=\"CONTROLS\" value=\"ImageWindow\"><param name=\"CONSOLE\" value=\"_master\"><param name=\"LOOP\" value=\"0\"><param name=\"NUMLOOP\" value=\"0\"><param name=\"CENTER\" value=\"0\"><param name=\"MAINTAINASPECT\" value=\"\\1\"><param name=\"BACKGROUNDCOLOR\" value=\"#000000\"></object><br><object classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA height=32 id=Player2 width=316 VIEWASTEXT><param name=\"_ExtentX\" value=\"18256\"><param name=\"_ExtentY\" value=\"794\"><param name=\"AUTOSTART\" value=\"1\"><param name=\"SHUFFLE\" value=\"0\"><param name=\"PREFETCH\" value=\"0\"><param name=\"NOLABELS\" value=\"0\"><param name=\"CONTROLS\" value=\"controlpanel\"><param name=\"CONSOLE\" value=\"_master\"><param name=\"LOOP\" value=\"0\"><param name=\"NUMLOOP\" value=\"0\"><param name=\"CENTER\" value=\"0\"><param name=\"MAINTAINASPECT\" value=\"0\"><param name=\"BACKGROUNDCOLOR\" value=\"#000000\"><param name=\"SRC\" value=\"\\1\"></object><br><script language=javascript>function FullScreen(){document.Player.SetFullScreen();}</script><input type='button' onclick='javascript:FullScreen()' value='$lang[full_screen]'>",$message,$db_cvtimes);
		}else{
			$message = preg_replace("/\[wmv\](.+?)\[\/wmv\]/is","<img src='$imgpath/$stylepath/file/music.gif' align='absbottom'> <a target=_blank href='\\1'>\\1</a>",$message,$db_cvtimes);
			$message = preg_replace("/\[rm\](.+?)\[\/rm\]/is","<img src='$imgpath/$stylepath/file/music.gif' align='absbottom'> <a target=_blank href='\\1'>\\1</a>",$message,$db_cvtimes);
		}
		if ($allow['iframe']) {
			$message = preg_replace("/\[iframe\](.+?)\[\/iframe\]/is","<IFRAME SRC=\\1 FRAMEBORDER=0 ALLOWTRANSPARENCY=true SCROLLING=YES WIDTH=97% HEIGHT=340></IFRAME>",$message,$db_cvtimes);
		}else{
			$message = preg_replace("/\[iframe\](.+?)\[\/iframe\]/is","Iframe Close: <a target=_blank href='\\1'>\\1</a>",$message,$db_cvtimes);
		}
	}
	if(is_array($phpcode_htm)){
		foreach($phpcode_htm as $key=>$value){
			$message=str_replace("<\twind_phpcode_$key\t>",$value,$message);
		}
	}
	return $message;
}
function postcache($key,$type){
	if($type==1){
		global $face,$imgpath;
		return "<img src='$imgpath/post/smile/{$face[$key]}'>";
	}elseif($type==2){
		global $act,$motion,$imgpath;
		return "<br>$act {$motion[$key][1]}<br><img src=$imgpath/post/act/{$motion[$key][2]}><br>";
	}
}
function copyctrl($bgcolor='#FFFFFF'){

	$lenth=10;
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.=chr(mt_rand(0,255));
	}
	$randval=str_replace('<','&lt;',$randval);
	return "<span style=\"font-size: 0pt;color:'$bgcolor'\" > $randval </span>&nbsp;<br>";
}

function attachment($message){
	global $attachper,$db_cvtimes;

	$attachper && $message=preg_replace("/\[attachment=([0-9]+)\]/eis","upload('\\1')",$message,$db_cvtimes);
	return $message;
}

function upload($aid){
	global $attachments,$aids;

	if($attachments[$aid]){
		$aids[]=$aid;
		return $attachments[$aid];
	} else{
		return "[attachment=$aid]";
	}
}

function size($size,$code,$allowsize){
	$allowsize && $size > $allowsize && $size = $allowsize;
	return "<font size='$size'>$code</font>";
}
function cvurl($http,$url='',$name=''){
	global $code_num,$code_htm;
	$code_num++;
	if(!$url){
		$url="<a href='http://www.$http' target=_blank>www.$http</a>";
	} elseif(!$name){
		$url="<a href='$http$url' target=_blank>$http$url</a>";
	} else{
		$url="<a href='$http$url' target=_blank>$name</a>";
	}
	$code_htm[0][$code_num]=$url;
	return "<\twind_code_$code_num\t>";
}

function nopic($url){
	global $code_num,$code_htm,$imgpath,$stylepath;
	$code_num++;
	$code_htm[-1][$code_num]="<img src='$imgpath/$stylepath/file/img.gif' align='absbottom' border=0> <a target=_blank href='$url'>img: $url</a>";
	return "<\twind_code_$code_num\t>";
}

function cvpic($url,$type='',$picwidth='',$picheight=''){
	global $db_bbsurl,$picpath,$attachpath,$code_num,$code_htm;
	$code_num++;
	if(strtolower(substr($url,0,4)) != 'http' && !$type)$url=$db_bbsurl.$url;
	if($picwidth && $picheight){
		$code = "<img src='$url' border=0 onclick=\"if(this.width>=$picwidth) window.open('$url');\" onload=\"if(this.width > $picwidth)this.width = $picwidth;if(this.height > $picheight) this.height = $picheight;\">";
	} else{
		$code = "<img src='$url' border=0 onclick=\"if(this.width>screen.width-461) window.open('$url');\" onload=\"if(this.width>screen.width-460)this.width=screen.width-460;\">";
	}
	$code_htm[-1][$code_num]=$code;
	if($type){
		return $code;
	} else{
		return "<\twind_code_$code_num\t>";
	}
}

function phpcode($code){
	global $phpcode_htm,$codeid;
	$code=str_replace("[attachment=","&#91;attachment=",$code);
	$codeid ++;
	$phpcode_htm[$codeid]="<div style=\"font-size:9px;margin-left:5px\"><b>CODE:</b></div><div class=quote id='code$codeid'>$code</div><div style=\"font-size:11px;margin-left:5px\"><a href=\"javascript:\"  onclick=\"CopyCode(document.getElementById('code$codeid'));\">[Copy to clipboard]</a></div>";

	return "<\twind_phpcode_$codeid\t>";
}

function qoute($code){
	global $code_num,$code_htm,$i_table;
	$code_num++;
	$code_htm[6][$code_num]="<div style=\"font-size:9px;margin-left:5px\"><b>QUOTE:</b></div><div class=quote>$code</div>";
	return "<\twind_code_$code_num\t>";
}

function post($code){
	global $SYSTEM,$postcode1,$postcode2,$attachper,$db,$tid,$fid,$winduid,$windid,$admincheck,$groupid,$tpc_author,$i_table;
	global $code_num,$code_htm,$lang;
	require_once GetLang('bbscode');
	$code_num++;
	$attachper=0;
	$rs = $db->get_one("SELECT count(*) AS count FROM pw_posts WHERE tid='$tid' AND authorid='$winduid'");
	if($rs['count']>0){
		$havereply='yes';
	}
	if($admincheck==1 || $SYSTEM['viewhide'] || $havereply=='yes' || $tpc_author==$windid){
		$attachper=1;
		$code_htm[3][$code_num]="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$lang[bbcode_hide1]<br><div class=quote>{$code}</div>";
	} else{
		$code_htm[3][$code_num]="<br><br><div class=quote>$lang[bbcode_hide2]</div><br><br>";
	}
	return "<\twind_code_$code_num\t>";
}

function hiden($rvrc,$code){
	global $hidecode1,$hidecode2,$hidecode3,$db,$groupid,$attachper,$userrvrc,$i_table;
	global $code_num,$code_htm,$lang;
	require_once GetLang('bbscode');
	$code_num++;
	$attachper=0;
	if($groupid!='guest'){
		global $admincheck,$userrvrc,$userpath,$windid,$tpc_author,$SYSTEM;
		$rvrc=trim(intval(stripslashes($rvrc)));
		if($windid!=$tpc_author && $userrvrc<$rvrc && $admincheck!=1 && !$SYSTEM['viewhide']){
			$code="<div class=quote>{$lang[bbcode_encode1]}{$rvrc}</div>";
		} else {
			$attachper=1;
			$code="&nbsp; &nbsp; &nbsp; {$lang[bbcode_encode2]}<br><div class=quote>{$code}</div>";
		}
	} else{
		$code=$lang['bbcode_encode3'];
	}
	$code_htm[4][$code_num]=$code;
	return "<\twind_code_$code_num\t>";
}

function sell($moneycost,$code){
	global $SYSTEM,$admincheck,$attachper,$windid,$tpc_author,$tpc_buy,$fid,$tid,$pid,$i_table,$manager,$groupid,$code_num,$code_htm,$lang,$db_credits,$db_bbsurl;
	list($db_moneyname,,,,,)=explode("\t",$db_credits);
	require_once GetLang('bbscode');
	$code_num++;
	$sellcheck=$attachper=0;
	$moneycost = (int)$moneycost;
	if($moneycost < 0){
		$moneycost = 0;
	}elseif ($moneycost > 1000){
		$moneycost = 1000;
	}elseif ($moneycost && !ereg("^[0-9]{0,}$",$moneycost)){
		$moneycost = 0;
	}
	$userarray=explode(',',$tpc_buy);
	foreach($userarray as $value){
		if($value){
			$count++;
			$buyers.="<OPTION value=>".$value."</OPTION>";
		}
	}
	!$count && $count=0;
	if ($groupid!='guest'&& ($SYSTEM['viewhide'] || $admincheck || $tpc_author==$windid || ($userarray && @in_array($windid,$userarray)))){
		$sellcheck=1;
	}
	$attachper=$sellcheck;
	$bbcode_sell_info=str_replace(array('$moneycost','$db_moneyname','$count'),array($moneycost,$db_moneyname,$count),$lang['bbcode_sell_info']);
	if($groupid!='guest' && $sellcheck==1){
		$printcode="&nbsp; &nbsp; &nbsp; &nbsp;<span class='bold'><font color='red'>{$bbcode_sell_info}</font></span><select name='buyers'><OPTION value=''>{$lang[bbcode_sell_buy]}</OPTION>$buyers</select><br><div class=quote>$code</div>";
	}else{
		$printcode="<br><span class='bold'><font color='red'>{$bbcode_sell_info}</font></span><select name='buyers'><OPTION value=''>{$lang[bbcode_sell_buy]}</OPTION><OPTION value=>-----------</OPTION>$buyers</select><input type='button' value='{$lang[bbcode_sell_submit]}' style='color: #000000; background-color: #f3f3f3; border-style: solid; border-width: 1' onclick=location.href='$db_bbsurl/job.php?action=buytopic&tid=$tid'><br><br><font color=red>{$lang[bbcode_sell_notice]}</font><br>";
	}
	$code_htm[5][$code_num]=$printcode;
	return "<\twind_code_$code_num\t>";
}

function showfacedesign($usericon){
	$user_a=explode('|',$usericon);
	if (strpos($usericon,'<')!==false || empty($user_a[0]) && empty($user_a[1])){
		return '<br><br>';
	}
	global $imgpath;
	if ($user_a[1]){
		if(!ereg("^http",$user_a[1])){
			$user_a[1]="$imgpath/upload/$user_a[1]";
		}
		if($user_a[2] && $user_a[3]){
			return "<img src='$user_a[1]' width=$user_a[2] height=$user_a[3] border=0>";
		}else{
			return "<img src='$user_a[1]' border=0>";
		}
	} else {
		return "<img src='$imgpath/face/$user_a[0]' border=0>";
	}
}
?>