<?php
!function_exists('readover') && exit('Forbidden');
$sendtourl="http://shanghai.kijiji.com.cn/classifieds/ClassiApiExCommand";

function fsockPost($url,$data) { 
	$web=parse_url($url);
	$data="xml=".$data;
	$web[port]="80";
	$fp=@fsockopen($web[host],$web[port],$errnum,$errstr,30); 
	if($fp) {
		fwrite($fp, "POST $web[path] HTTP/1.1\r\n"); 
		fwrite($fp, "Host: $web[host]\r\n"); 
		fwrite($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
		fwrite($fp, "Content-length: ".strlen($data)."\r\n"); 
		fwrite($fp, "Connection: close\r\n\r\n"); 
		fwrite($fp, $data."\r\n\r\n"); 
		while(!feof($fp)) {
			$info[]=@fgets($fp, 1024);
		} 
		fclose($fp); 
		$info=implode("\n",$info); 
	}
	return $info;
}
function init_data($email,$area_id,$category_id,$title,$description){
	$title		= GB2U($title);
	$description= GB2U($description);

$strPostXML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<SOAP:Envelope xmlns:SOAP=\"http://www.w3.org/2003/05/soap-envelope\" >
	<SOAP:Header  xmlns:m=\"http://www.kijiji.com/soap\">
		<m:command>post_ex</m:command> 
		<m:version>1</m:version> 
	</SOAP:Header>
	<SOAP:Body >
		<m:user xmlns:m=\"http://www.kijiji.com/soap\">
			<email>$email</email>
		</m:user>
		<m:ad xmlns:m=\"http://www.kijiji.com/soap\">
			<area_id>$area_id</area_id>
			<category_id>$category_id</category_id>
			<language_code>1</language_code>
			<format>1</format>
			<title>$title</title>
			<description><![CDATA[$description]]></description>
			<currency_id>14</currency_id>
			<price_type>3</price_type>
		</m:ad>
	</SOAP:Body>
</SOAP:Envelope>";

	return $strPostXML;
}
function GB2U($source) {
	if(trim($source)=="") return $source;
	$unicode = array();
	$tmp	 = openfile(R_P."require/gb-unicode.table");
	foreach($tmp as $key => $val){
		$unicode[hexdec(substr($val, 0, 6))] = substr($val, 7, 6);
	}
	$ret = $utf_8 = '';
	while ($source) {
		if (ord(substr($source, 0, 1)) > 127) {
			$tW		= substr($source, 0, 2);
			$source	= substr($source, 2, strlen($source));
			$utf_8	= "";
			$utf_8	= U2UTF8(hexdec($unicode[hexdec(bin2hex($tW)) - 0x8080]));
			if($utf_8!=""){
				for ($i=0;$i<strlen($utf_8);$i+=3)
					$ret .= chr(substr($utf_8,$i,3));
			}
		}else{
			$ret	.= substr($source,0,1);
			$source  = substr($source,1,strlen($source));
		}
	}
	return $ret;
}
function U2UTF8($u) {
	for ($i = 0;$i < count($u);$i++)
		$str = "";
	if ($u < 0x80) {
		$str .= $u;
	} else if ($u < 0x800) {
		$str .= (0xC0 | $u >> 6);
		$str .= (0x80 | $u & 0x3F);
	} else if ($u < 0x10000) {
		$str .= (0xE0 | $u >> 12);
		$str .= (0x80 | $u >> 6 & 0x3F);
		$str .= (0x80 | $u & 0x3F);
	} else if ($u < 0x200000) {
		$str .= (0xF0 | $u >> 18);
		$str .= (0x80 | $u >> 12 & 0x3F);
		$str .= (0x80 | $u >> 6 & 0x3F);
		$str .= (0x80 | $u & 0x3F);
	}
	return $str;
}
?>