<?php
!function_exists('readover') && exit('Forbidden');

function download($code){
	global $imgpath,$stylepath,$lang;
	include_once(GetLang('bbscode'));
	$size		= GetStr($code,'size');
	$language	= GetStr($code,'language');
	$type		= GetStr($code,'type');
	$os			= GetStr($code,'os');
	$date		= GetStr($code,'date');
	$author		= GetStr($code,'author');
	$level		= GetStr($code,'level');
	$publish	= GetStr($code,'publish');
	$publishlink= GetStr($code,'publishlink');
	$preview	= GetStr($code,'preview');
	$hits		= GetStr($code,'hits');
	$descrip	= GetStr($code,'descrip');
	$link		= GetStr($code,'link');
	if($publishlink){
		$publishlink= str_replace(array('[url]','[/url]'),'',$publishlink);
		$publish	= str_replace(array('[url]','[/url]'),'',$publish);
		$publish	= "<a href=\"$publishlink\" target=\"_blank\">$publish</a>";
	}
	$links	= explode(',',str_replace(array('[url]','[/url]'),'',$link));
	$link	= '';
	foreach($links as $key=>$val){
		list($name,$url)=explode('|',$val);
		if($url){
			$link .= "<a href=\"$url\">$name</a> ";
		}else{
			$link .= "$name ";
		}
	}

	$descrip=str_replace('\"','"',$descrip);
	$str = '<br>';
	$str .= "$lang[size]$size<br>";
	$str .= "$lang[language]$language<br>";
	$str .= "$lang[type]$type<br>";
	$str .= "$lang[os]$os<br>";
	$str .= "$lang[date]$date<br>";
	$str .= "$lang[author]$author<br>";
	$str .= "$lang[level]$level<br>";
	$str .= "$lang[publish]$publish<br>";
	$str .= "$lang[preview]$preview<br>";
	$str .= "$lang[link]$link<br><br>";
	$str .= "$lang[descrip]$descrip<br>";
	return $str;
}
function GetStr($code,$tag){
	$tmp = substr($code,strpos($code,"($tag)") + strlen("($tag)"));
	return substr($tmp,0,strpos($tmp,"(/$tag)"));
}
?>