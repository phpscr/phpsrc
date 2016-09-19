<?php
/**
 * Useforum  Copyright (C) 2010-2013 基础函数库
 * 添加日期 2013-7-6 19:15 GW
 * 修订时版本0.3.2
 */


/*
 * 智能时间函数
 * 添加日期 2013-6-26 GW
 */
function myDate($time = null){
	if( null == $time )$time = time(); // 默认是当前时间
	if( $time > (time() - 5) ){
		return "刚才";
	}elseif( $time > (time() - 60) ){
		$sec = time() - $time;
		return $sec."秒前";
	}elseif( $time > (time() - 3600) ){
		$min = floor(( time() - $time)/60);
		return $min."分钟前";
	}elseif( $time > (time() - 3600 * 24) ){
		$hour = floor((time() - $time)/3600);
		return $hour."小时前";
	}elseif( floor(time()/(24*3600))-floor($time/(24*3600)) == 1){
		return "昨天 ".date("H:i", $time);	
	}elseif( floor(time()/(24*3600))-floor($time/(24*3600)) == 2){
		return "前天 ".date("H:i", $time);
	}else{
		return date("Y-m-d H:i", $time);
	}
}
// 将mydate注册到模板中使用，所以需要加入tpl_mydate函数进行一次封装
function tpl_myDate($params){
	return myDate($params['time']);
}
// 使用spAddViewFunction来注册，模板中使用thedate来进行调用
spAddViewFunction('theDate','tpl_myDate');
/*
 * 函数功能：实现了完整的HTML标签截取而不会错位。
 * 支持中英文混合，可以运行于支持mb_string，iconv函数和通用环境。
 * 作者：Harry Zhang
 * 邮箱：korsenzhang@yahoo.com.cn
*/
function cutString($str,$length=300){
	$str = preg_split("/(<[^>]+?>)/si", $str, -1, PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows = 0;
	$outstr = "";
	$wordend = false;
	$beginTags = 0;
	$endTags = 0;
	foreach($str as $value)
	{
		if(trim($value) == "") continue;
		if(strpos(";$value", "<")>0)
		{
			if(trim($value) == $length)
			{
				$wordend = true;
				continue;
			}
			if($wordend == false)
			{
				$outstr .= $value;
				if(!preg_match("/<img([^>]+?)>/is", $value) && !preg_match("/<param([^>]+?)>/is", $value) && !preg_match("/<!([^>]+?)>/is", $value) && !preg_match("/<br([^>]*?)>/is", $value) && !preg_match("/<hr([^>]*?)>/is", $value))
				{
					$beginTags++;
				}
			}elseif(preg_match("/<\/([^>]+?)>/is", $value, $matches))
			{
				$endTags++;
				$outstr .= $value;
				if($beginTags == $endTags && $wordend == true) break;
			}else{
				if(!preg_match("/<img([^>]+?)>/is", $value) && !preg_match("/<param([^>]+?)>/is", $value) && !preg_match("/<!([^>]+?)>/is", $value) && !preg_match("/<br([^>]*?)>/is", $value) && !preg_match("/<hr([^>]*?)>/is", $value))
				{
					$beginTags++;
					$outstr .= $value;
				}
			}
		}else{
			if(is_numeric($length))
			{
				$curLength = getStringLength($value);
				$maxLength = $curLength + $wordrows;
				if($wordend == false)
				{
					if($maxLength > $length)
					{
						$outstr .= subString($value, 0, $length-$wordrows)."...";
						$wordend = true;
					}else{
						$wordrows = $maxLength;
						$outstr .= $value;
					}
				}
			}else{
				if($wordend == false) $outstr .= $value;
			}
		}
	}
	while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is", $outstr))
	{
		$outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html", $outstr);
	}
	if(strpos(";".$outstr, "[html_") > 0)
	{
		$outstr = str_replace("[html_&lt;]", "<",$outstr);
		$outstr = str_replace("[html_&gt;]", ">",$outstr);
	}
	return $outstr;
}
function strip_empty_html($matches)
{
	$arr_tags1 = explode(" ", $matches[1]);
	if($arr_tags1[0] == $matches[2])
	{
		return "";
	}else{
		$matches[0] = str_replace("<", "[html_&lt;]", $matches[0]);
		$matches[0] = str_replace(">", "[html_&gt;]", $matches[0]);
		return $matches[0];
	}
}
function getStringLength($text)
{
	if(function_exists('mb_substr'))
	{
		$length = mb_strlen($text,'UTF-8');
	}elseif(function_exists('iconv_substr')){
		$length = iconv_strlen($text,'UTF-8');
	}else{
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
		$length = count($ar[0]);
	}
	return $length;
}

function subString($text, $start=0, $limit=12)
{
	if(function_exists('mb_substr'))
	{
		$more = (mb_strlen($text, 'UTF-8') > $limit) ? TRUE : FALSE;
		$text = mb_substr($text, 0, $limit, 'UTF-8');
		return $text;
	}elseif(function_exists('iconv_substr')){
		$more = (iconv_strlen($text, 'UTF-8') > $limit) ? TRUE : FALSE;
		$text = iconv_substr($text, 0, $limit, 'UTF-8');
		return $text;
	}else{
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
		if(func_num_args() >= 3)
		{
			if(count($ar[0]) > $limit){
				$more = TRUE;
				$text = join("", array_slice($ar[0], 0, $limit));
			}else{
				$more = FALSE;
				$text = join("", array_slice($ar[0], 0, $limit));
			}
		}else{
			$more = FALSE;
			$text =  join("", array_slice($ar[0], 0));
		}
		return $text;
	}
}
// 将mydate注册到模板中使用，所以需要加入tpl_mydate函数进行一次封装
function tpl_cutString($params){
	return cutString($str=$params['str'],$length=$params['length']);
}
// 使用spAddViewFunction来注册，模板中使用thedate来进行调用
spAddViewFunction('cutString','tpl_cutString');
//字节转换
function formatBytes($params) {
	$bytes = $params['size'];
	if($bytes >= 1073741824) {
		$bytes = round($bytes / 1073741824 * 100) / 100 . 'GB';
	} elseif($bytes >= 1048576) {
		$bytes = round($bytes / 1048576 * 100) / 100 . 'MB';
	} elseif($bytes >= 1024) {
		$bytes = round($bytes / 1024 * 100) / 100 . 'KB';
	} else {
		$bytes = $bytes . '字节';
	}
	return $bytes;
}
spAddViewFunction('formatBytes','formatBytes');
/*
 * 获取真实IP
 * 添加日期 2011 GW
 */
function getIP(){
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}else{
		if (isset($_SERVER["HTTP_CLIENT_IP"])){
	        $ip = $_SERVER["HTTP_CLIENT_IP"];
	    }else{
	        $ip = $_SERVER["REMOTE_ADDR"];
	        }
	}
	return $ip;
}
/*
 * 正则过滤
 * 添加日期 2012 GW
 */
function strReplaces($str){
	$str=preg_replace("/\s+/", " ", $str); //过滤多余回车 
	$str=preg_replace("/<[ ]+/si","<",$str); //过滤<__("<"号后面带空格) 

	$str=preg_replace("/<\!--.*?-->/si","",$str); //注释 
	$str=preg_replace("/<(\!.*?)>/si","",$str); //过滤DOCTYPE 
	$str=preg_replace("/<(\/?html.*?)>/si","",$str); //过滤html标签 
	$str=preg_replace("/<(\/?head.*?)>/si","",$str); //过滤head标签 
	$str=preg_replace("/<(\/?meta.*?)>/si","",$str); //过滤meta标签
	$str=preg_replace("/<(\/?body.*?)>/si","",$str); //过滤body标签 
	$str=preg_replace("/<(\/?link.*?)>/si","",$str); //过滤link标签 
	$str=preg_replace("/<(\/?form.*?)>/si","",$str); //过滤form标签 
	$str=preg_replace("/cookie/si","COOKIE",$str); //过滤COOKIE标签 

	$str=preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si","",$str); //过滤applet标签 
	$str=preg_replace("/<(\/?applet.*?)>/si","",$str); //过滤applet标签 

	$str=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si","",$str); //过滤style标签
	$str=preg_replace("/<(\/?style.*?)>/si","",$str); //过滤style标签

	$str=preg_replace("/<(input.*?)>(.*?)<(\/input.*?)>/si","",$str); //过滤input标签
	$str=preg_replace("/<(\/?input.*?)>/si","",$str); //过滤input标签

	$str=preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si","",$str); //过滤title标签
	$str=preg_replace("/<(\/?title.*?)>/si","",$str); //过滤title标签 

	$str=preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si","",$str); //过滤object标签 
	$str=preg_replace("/<(\/?objec.*?)>/si","",$str); //过滤object标签 

	$str=preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si","",$str); //过滤noframes标签 
	$str=preg_replace("/<(\/?noframes.*?)>/si","",$str); //过滤noframes标签 

	$str=preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si","",$str); //过滤frame标签 
	$str=preg_replace("/<(\/?i?frame.*?)>/si","",$str); //过滤frame标签 

	$str=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si","",$str); //过滤script标签 
	$str=preg_replace("/<(\/?script.*?)>/si","",$str); //过滤script标签

	$str=preg_replace("/<(div.*?)>(.*?)<(\/div.*?)>/si","",$str); //过滤div标签
	$str=preg_replace("/<(\/?div.*?)>/si","",$str); //过滤div标签

	$str=preg_replace("/javascript/si","Javascript",$str); //过滤script标签 
	$str=preg_replace("/vbscript/si","Vbscript",$str); //过滤script标签
	$str=preg_replace("/on([a-z]+)\s*=/si","On\\1=",$str); //过滤script标签 
	$str=preg_replace("/&#/si","&＃",$str); //过滤script标签，如javAsCript:alert(
	if($str["title"]){
		$str["title"] = clearLabel($str["title"]);
	}
	return $str;
}
/*
 * 最后登录时间获取
 * 通过检查缓存返回时间或空。
 * 添加日期 13-7-15 GW
 */
function tpl_lastLogin($params){
	$a = spAccess("r",$params['uname']);
	if($a['last']){
		$date = date("Y-m-d", $a['last']);
		return "最后登录 {$date}";
	}
}
spAddViewFunction('lastLogin','tpl_lastLogin');

/*用户名获取资料页地址*/
function tpl_getUrl($params){
		$url = spUrl("user","profile",array('uname'=>$params['uname']));
		return "<a href=\"{$url}\">{$params['uname']}</a>";
}
spAddViewFunction('getUrl','tpl_getUrl');
function getUrl($uname){
	return tpl_getUrl(array("uname"=>$uname));
}

/*
 * 淘宝IP地址库函数
 * 通过淘宝API获得JSON数据，转码为城市信息。
 * 添加日期 13-7-20 GW
 */
function getCity($params){
	if (file_exists("http://ip.taobao.com/service/getIpInfo.php?")):
		$url="http://ip.taobao.com/service/getIpInfo.php?ip=".$params['ip'];
		$ip=json_decode(file_get_contents($url));
		if((string)$ip->code=='1'){
		  return false;
		}
		$data = (array)$ip->data;
		return $data['city'];
	endif;
}
spAddViewFunction('getCity','getCity');
/*
 * 分页函数
 * 添加日期 13-7-25 GW
 */
function tpl_pager($param){
	$pager = $param['pager'];
	$c = $param["c"];
	$a = $param["a"];
	$idname = $param["idname"];
	$id = $param["id"];
	$b = '&'.$param["other"];
	if ($pager.current_page != $pager.first_page):
		$u1 = spUrl($c,$a,array($idname => $id,"page"=>$pager["first_page"]));
		$u2 = spUrl($c,$a,array($idname => $id,"page"=>$pager["prev_page"]));
		print <<<PAGE
<a href="{$u1}{$b}">首页</a>
<a href="{$u2}{$b}">上一页</a>\n
PAGE;
	endif;
	if($pager["current_page"] > 3 and $pager["total_page"]>10 )echo"<span class=\"ellipsis\">…</span>\n";
	foreach($pager["all_pages"] as $thepage):
		if ($thepage != $pager["current_page"]):
			$u3 = spUrl($c,$a,array($idname => $id,"page"=>$thepage));
			echo "<a href=\"{$u3}{$b}\">{$thepage}</a>\n" ;
		else:
			echo "<b>{$thepage}</b>\n";
		endif;
	endforeach;
	if($pager["total_page"] - $pager["current_page"] > 3 and $pager["total_page"]>10 )echo"<span class=\"ellipsis\">…</span>\n";
	if ($pager['current_page'] != $pager['last_page']):
		$u4 = spUrl($c,$a,array($idname => $id,"page"=>$pager["next_page"]));
		$u5 = spUrl($c,$a,array($idname => $id,"page"=>$pager["last_page"]));
		print <<<PAGE
<a href="{$u4}{$b}">下一页</a>
<a href="{$u5}{$b}">尾页</a>\n
PAGE;
	endif;
	print <<<PAGE
<span class="total">{$pager["total_count"]}条/{$pager["total_page"]}页</span>\n
PAGE;
	return;
}
spAddViewFunction('pager','tpl_pager');

/*
 * 提取@后用户列表
 * 添加日期 13-7-25 ZY
 * ZY945.COM
 */
function atSomeone ($content){
	if (false !== strpos($content, '@')) {
		if (preg_match_all('~\@([\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+|[\xa3\xac]|[\xef\xbc\x8c]|[\,\.\;\[\#])~', $content, $match)) {
			if (is_array($match[1]) && count($match[1])) {
				foreach ($match[1] as $k => $v) {
					$v = trim($v);
					if ('　' == substr($v, -2)) {
						$v = substr($v, 0, -2);
					}

					if ($v && strlen($v) < 16) {
						$match[1][$k] = $v;
					}
				}
			}
		}
		$list = array_unique($match[1]);
		return $list;
	}
}

/*
 * 清空字符串中的HTML标签
 * 添加日期 13-7-26 GW
 */
function clearLabel($html){
	$search = array ("'<script[^>]*?>.*?</script>'si", "'<[/!]*?[^<>]*?>'si", "'([rn])[s]+'", "'&(quot|#34);'i", "'&(amp|#38);'i", "'<'i", "'>'i", "'script'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i", "'&#(d+);'e");
	$replace = array ("", "", "\1", "\"", "&", "&lt;", "&gt;", "s c r i p t;", " ", chr(161), chr(162), chr(163), chr(169), "chr(\1)");
	return preg_replace($search, $replace, $html);
}

/*
 * 发送动态
 * 添加日期 13-8-3 GW
 */
function sendFeed($content,$url){
	$f = spClass('lib_feed');
	$newrow = array(
		"content" => $content,
		"url" =>$url,
		"uname" =>$_SESSION["userinfo"]["uname"] ,
		"ctime" =>$_SERVER['REQUEST_TIME'],
		"avatar" =>$_SESSION["userinfo"]["avatar"]
	);
	$f -> create($newrow);
	if($f->findCount() >3000){
		$last = $f->find(null,"ctime ASC","fid");
		$f->deleteByPk($last['fid']);
	}
}

/*
 * 获取留言所在页数
 * 添加日期 13-8-31
 */
function messageLocation ($uid){
	$m = spClass('lib_userBoard');
	$sum = $m->findCount(array('uid' =>$uid));
	$location = floor($sum/10) + 1;
	return $location;
}
/*
 * 检测输出头像函数
 * 添加日期 14-7-4 GW
 */
function avatar($avatar){
	if($avatar){
		/*if(file_exists($avatar)){
			return $avatar;
			}else{
			   return null;
		   }*/return $avatar;
	}else{
			return null;
	}
}
/*写入文件

 */
function fileWrite($file,$dir,$data,$isphp=1){
		
	$dfile = $dir.'/'.$file;
	

	//同时保存文件
	!is_dir($dir)?mkdir($dir,0777):'';
	if(is_file($dfile)) unlink($dfile);
	if($isphp == 1){
		$data = "<?php\ndefined('APP_PATH') or die('Access Denied.');\nreturn ".var_export($data,true).";";
	}
	
	file_put_contents($dfile,$data);
	
	return true;
}

/*
 *读取文件 
 *$dfile 文件
 */
function fileRead($dfile){	
		if(is_file($dfile)) return include $dfile;
	
	

}

/*
 * mail发送
 * 添加日期 14-7-11 ZY
 * ZY945.COM
 */
	function postMail($sendmail,$subject,$content){
		
		$options = fileRead('./include/Extensions/mail/mail_options.php');
		require_once './include/Extensions/mail/PHPMailer/class.phpmailer.php';
		$mail = new PHPMailer();

		//邮件配置
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP(); 
		$mail->SMTPDebug  	= 1;
		$mail->SMTPAuth   	= true;           
		$mail->Host       		= $options['mailhost']; 
		$mail->Port       		= $options['mailport']; 
		$mail->Username   	= $options['mailuser']; 
		$mail->Password   	= $options['mailpwd']; 

		//POST过来的信息
		$frommail		= $options['mailuser'];
		$fromname	= $sitename;
		$replymail		= $options['mailuser'];
		$replyname	= $sitename;
		$sendname	= '';

		if(empty($frommail) || empty($subject) || empty($content) || empty($sendmail)){
			return '0';
		}else{

			//邮件发送
			$mail->SetFrom($frommail, $fromname);
			$mail->AddReplyTo($replymail,$replyname);
			$mail->Subject    = $subject;
			$mail->AltBody    = "要查看邮件，请使用HTML兼容的电子邮件阅读器!"; 
			//$mail->MsgHTML(eregi_replace("[\]",'',$content));
			$mail->MsgHTML(strtr($content,'[\]',''));
			$mail->AddAddress($sendmail, $sendname);
			$mail->Send();
			
			return '1';
			
		}
		}

spAddViewFunction('avatar','avatar');