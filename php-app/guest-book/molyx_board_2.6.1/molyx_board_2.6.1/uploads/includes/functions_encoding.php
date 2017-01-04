<?php
class Encoding
{
    var $GetEncoding="1";
    var $ToEncoding="3";
	var $UnicodeString="";
	var $Encodings=array("1"=>"GBK","2"=>"UTF-8","3"=>"UTF-16BE","4"=>"UTF-16LE","5"=>"BIG5");

	function Encoding()
	{
		$this->FilePath = ROOT_PATH."includes/encoding/";
	}

	function EncodeString($String, $GetEncoding, $ToEncoding)
	{
		$this->Getkey = $this->CheckEncoding($GetEncoding, "get");
		if ($this->Getkey) {
		    $this->GetEncoding=$this->Getkey;
		}
		$this->tokey = $this->CheckEncoding($ToEncoding,"to");
		if ($this->tokey) {
		    $this->ToEncoding=$this->tokey;
		}
		if (!$this->GetEncoding OR !$this->ToEncoding) {
			return $String;
		}
	    if ($this->GetEncoding=="1") {
	        $this->UnicodeString=$this->GBKToUnicode($String);
	    }
		elseif ($this->GetEncoding=="2") {
		    $this->UnicodeString=$this->UTFToUnicode($String);
		}
		elseif ($this->GetEncoding=="3") {
		    $this->UnicodeString=$String;
		}
		elseif ($this->GetEncoding=="4") {
		    $this->UnicodeString=$this->ChangeByte($String);
		}
		elseif ($this->GetEncoding=="5") {
		    $this->UnicodeString=$this->BIGToUnicode($String);
		}
		if ($this->ToEncoding=="1") {
	        return $this->UnicodeToGBK($this->UnicodeString);
	    }
		elseif ($this->ToEncoding=="2") {
		    return $this->UnicodeToUTF($this->UnicodeString);
		}
		elseif ($this->ToEncoding=="3") {
		    return $this->UnicodeString;
		}
		elseif ($this->ToEncoding=="4") {
		    return $this->ChangeByte($this->UnicodeString);
		}
		elseif ($this->ToEncoding=="5") {
		    return $this->UnicodeToBIG($this->UnicodeString);
		}
	}

	function GBKToUnicode(&$String)
	{
	    $UnicodeData=file($this->FilePath."gbkunicode.data");
		$ReturnString="";
		$StringLength=strlen($String);
		$p="";
		$q="";
		for($i=0;$i<$StringLength;$i++){
			if(($p=ord(substr($String,$i,1)))>128){
				$q=ord(substr($String,++$i,1));
				if ($p>254) {
				    $ReturnString.="003f";
				}
				elseif ($q<64||$q>254) {
				    $ReturnString.="003f";
				}
				else {
					$q=($q-64)*4;
					$ReturnString.=substr($UnicodeData[$p-128],$q,4);
				}
			}
			else {
				if ($p==128) {
				    $ReturnString.="20ac";
				}
				else {
					$ReturnString.="00";
					$ReturnString.=dechex($p);
				}
			}
		}
		return $this->hex2bin($ReturnString);
	}
	function BIGToUnicode(&$String)
	{
	    $UnicodeData=file($this->FilePath."bigunicode.data");
		$ReturnString="";
		$StringLength=strlen($String);
		$p="";
		$q="";
		for($i=0;$i<$StringLength;$i++){
			if(($p=ord(substr($String,$i,1)))>128){
				$q=ord(substr($String,++$i,1));
				if ($p>249) {
				    $ReturnString.="003f";
				}
				elseif ($q<64||$q>254) {
				    $ReturnString.="003f";
				}
				else {
					$q=($q-64)*4;
					$ReturnString.=substr($UnicodeData[$p-160],$q,4);
				}
			}
			else {
				$ReturnString.="00";
				$ReturnString.=dechex($p);
			}
		}
		return $this->hex2bin($ReturnString);
	}
	function UnicodeToGBK(&$String)
	{
	    $GBKData=file($this->FilePath."unicodegbk.data");
		$ReturnString="";
		$StringLength=strlen($String);
		$p="";
		$q="";
		$temp="";
		for($i=0;$i<$StringLength;$i++){
			$p=ord(substr($String,$i++,1));
			if ($i==$StringLength) {
			    $temp=dechex($p);
				if (strlen($temp)<2) {
				    $temp="0".$temp;
				}
				$ReturnString.=$temp;
				continue;
			}
			$q=ord(substr($String,$i,1));
			if ($p==0&&$q<127) {
			    $temp=dechex($q);
				if (strlen($temp)<2) {
				    $temp="0".$temp;
				}
				$ReturnString.=$temp;
				continue;
			}
			$p++;
			$begin=hexdec(substr($GBKData[$p],0,2));
			if (strlen($GBKData[$p])<3||$q<$begin||$q>hexdec(substr($GBKData[$p],2,2))) {
			    $ReturnString.="3f";
				continue;
			}
			$q*=4;
			$q-=$begin*4;
			$temp=substr($GBKData[$p],$q+4,2);
			if ($temp=="00") {
			    $ReturnString.=substr($GBKData[$p],$q+6,2);
			}
			else {
			    $ReturnString.=$temp.substr($GBKData[$p],$q+6,2);
			}
		}
		return $this->hex2bin($ReturnString);
	}
	function UnicodeToBIG(&$String)
	{
	    $BIGData=file($this->FilePath."unicodebig.data");
		$ReturnString="";
		$StringLength=strlen($String);
		$p="";
		$q="";
		$temp="";
		for($i=0;$i<$StringLength;$i++){
			$p=ord(substr($String,$i++,1));
			if ($i==$StringLength) {
			    $temp=dechex($p);
				if (strlen($temp)<2) {
				    $temp="0".$temp;
				}
				$ReturnString.=$temp;
				continue;
			}
			$q=ord(substr($String,$i,1));
			if ($p==0&&$q<127) {
			    $temp=dechex($q);
				if (strlen($temp)<2) {
				    $temp="0".$temp;
				}
				$ReturnString.=$temp;
				continue;
			}
			$p++;
			$begin=hexdec(substr($BIGData[$p],0,2));
			if (strlen($BIGData[$p])<3||$q<$begin||$q>hexdec(substr($BIGData[$p],2,2))) {
			    $ReturnString.="3f";
				continue;
			}
			$q*=4;
			$q-=$begin*4;
			$temp=substr($BIGData[$p],$q+4,2);
			if ($temp=="00") {
			    $ReturnString.=substr($BIGData[$p],$q+6,2);
			}
			else {
			    $ReturnString.=$temp.substr($BIGData[$p],$q+6,2);
			}
		}
		return $this->hex2bin($ReturnString);
	}
	function UnicodeToUTF(&$String)
	{
		$len=strlen($String);
		$ReturnString="";
		for ($x=0;$x<$len;$x++) {
		    $Char=substr($String,$x++,1);
			if ($x==$len) {
			    $ReturnString.=bin2hex($Char);
				continue;
			}
			$Char.=substr($String,$x,1);
			$hex=bin2hex($Char);
			$dec=hexdec($hex);
			$bin=decbin($dec);
			$temp="";
			if($dec>0x7f){
				$binlen=strlen($bin);
				for ($i=0,$n=16-$binlen;$i<$n;$i++) {
				    $bin="0".$bin;
				}
				$temp.="1110".substr($bin,0,4);
				$temp.="10".substr($bin,4,6);
				$temp.="10".substr($bin,10,6);
				$temp=dechex(bindec($temp));
			}
			else {
				$temp=substr($hex,2,2);
			}
			$ReturnString.=$temp;
		}
		return $this->hex2bin($ReturnString);
	}
	function ChangeByte(&$String)
	{
	    $len=strlen($String);
		$ReturnString="";
		for ($i=0;$i<$len;$i++) {
			if ($i+1!=$len) {
			    $ReturnString.=substr($String,$i+1,1).substr($String,$i++,1);
			}
			else {
			    $ReturnString.=substr($String,$i,1);
			}
		}
		return $ReturnString;
	}
	function UTFToUnicode(&$String)
	{
	    $UTFlen=strlen($String);
		$x="";
		$y="";
		$z="";
		$ReturnString="";
		for ($i=0;$i<$UTFlen;$i++) {
		    if(($x=ord(substr($String,$i,1)))>128){
				if ($i+1==$UTFlen) {
				    $ReturnString.=dechex($x);
					continue;
				}
				$y=ord(substr($String,++$i,1));
				if ($i+1==$UTFlen) {
				    $ReturnString.=dechex($x).dechex($y);
					continue;
				}
				$x=decbin($x);
				$y=decbin($y);
				$z=decbin(ord(substr($String,++$i,1)));
				$temp=dechex(bindec(substr($x,4,4).substr($y,2,4).substr($y,6,2).substr($z,2,6)));
				$len=strlen($temp);
				for ($j=0,$n=4-$len;$j<$n;$j++) {
				    $temp="0".$temp;
				}
				$ReturnString.=$temp;
			}
			else {
				$ReturnString.="00";
			    $ReturnString.=dechex($x);
			}
		}
		return $this->hex2bin($ReturnString);
	}
	function hex2bin(&$String)
	{
		$ReturnString="";
	    $len=strlen($String);
		for($i=0;$i<$len;$i+=2) {
			$ReturnString.= pack("C",hexdec(substr($String,$i,2)));
		}
		return $ReturnString;
	}
	function CheckEncoding($Encoding, $type="get")
	{
		if ($this->Getkey AND $type=="get") return $this->Getkey;
		if ($this->tokey AND $type=="to") return $this->tokey;
		reset($this->Encodings);
		foreach ($this->Encodings AS $key => $value) {
	        if ($Encoding==$value) {
	            return $key;
	        }
		}
		return false;
	}
}
?>