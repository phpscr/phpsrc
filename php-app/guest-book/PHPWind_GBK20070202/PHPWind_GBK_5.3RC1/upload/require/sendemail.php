<?php
!function_exists('readover') && exit('Forbidden');
include_once(D_P.'data/bbscache/mail_config.php');

$M_db= new Mailconfig(
	array(
		'method'=> $ml_mailmethod,
		'host'	=> $ml_smtphost,
		'port'	=> $ml_smtpport,
		'auth'	=> $ml_smtpauth,
		'from'	=> $ml_smtpfrom,
		'user'	=> $ml_smtpuser,
		'pass'	=> $ml_smtppass,
		'smtphelo'=>$ml_smtphelo,
		'smtpmxmailname' =>$ml_smtpmxmailname,
		'mxdns'=>$ml_mxdns,
		'mxdnsbak'=>$ml_mxdnsbak
	)
);
Class Mailconfig {
	var $S_method = 1;
	var $smtp;
	function Mailconfig($smtp=array()){
		$this->S_method = $smtp['method'];
		if ($this->S_method == 1){
			//²»ÓÃÉèÖÃ
		} elseif ($this->S_method == 2){
			$this->smtp['host'] = $smtp['host'];
			$this->smtp['port'] = $smtp['port'];
			$this->smtp['auth'] = $smtp['auth'];
			$this->smtp['from'] = $smtp['from'];
			$this->smtp['user'] = $smtp['user'];
			$this->smtp['pass'] = $smtp['pass'];
		} elseif ($this->S_method == 3){
			$this->smtp['port'] = $smtp['port'];
			$this->smtp['auth'] = $smtp['auth'];
			$this->smtp['from'] = $smtp['from'];
			$this->smtp['smtphelo']=$smtp['smtphelo'];
			$this->smtp['smtpmxmailname']=$smtp['smtpmxmailname'];
			$this->smtp['mxdns']=$smtp['mxdns'];
			$this->smtp['mxdnsbak']=$smtp['mxdnsbak'];
			//hacker
		} else {
			//hacker
		}
	}
	function mailmx($email,$retrys=3){
		$domain=substr($email,strpos($email,'@')+1);
		@include(D_P.'data/bbscache/mx_config.php');
		//@include('mx_config.php');

		if(!$_MX[$domain] || $timestamp - filemtime(D_P.'data/bbscache/mx_config.php') > 3600*24*10){
			for($i=0;$i<$retrys;$i++){
			  $result = $this->GetMax($domain);
			  if($result !== false){
				  $_MX[$domain]=$result;
				  writeover(D_P.'data/bbscache/mx_config.php',"<?php\r\n\$_MX=".pw_var_cv($_MX).";\r\n?>");
				  $this->smtp['tomx']=$result;
				  return true;
			  }
			}
			return false;
		}else{
			$this->smtp['tomx']=$_MX[$domain];
			return true;
		}
	}
	function GetMax($maildomain){
		$header=pack("H*","000101000001000000000000");
		$end=pack("H*","00000f0001");
		$domain=explode(".",$maildomain);
		$ques='';
		foreach($domain as $value){
			$ques .=pack("Ca*",strlen($value),$value);
		}

		$fp = ($fp=fsockopen("udp://".$this->smtp['mxdns'], 53, $errno, $errstr , 30)) !== false ? $fp : fsockopen("udp://".$this->smtp['mxdnsbak'], 53, $errno, $errstr , 30);
		if (!$fp) {
		   return false;
		} else {
		   fwrite($fp, $header.$ques.$end);
		   $data=fread($fp,12);
		   $q=unpack("n*",$data);
		   
		   if(array_shift($q) !=0x0001){
			   return false;
		   }

		   if((array_shift($q) & 0x800F) !=0x8000){
				return false;
		   }

		   if(array_shift($q) !=0x0001){
				return false;
		   }
			
		   $anum=array_shift($q);
		   if( $anum < 0x0001){
				return false;
		   }
		   $aunum=array_shift($q);
		   $aanum=array_shift($q);
		   
		   if(fread($fp,strlen($ques)+5) !== $ques.$end){
			   return false;
		   }
		   $data .= $ques.$end;
		   
		   $rs=array();
		   for($i=0;$i<$anum;$i++){
			   $mxanwer=array();
			   $tdata=fread($fp,1);
			   $tmx=array();
			   $tna=array();
			   $tpre=65535;
			   $compresspos=-1;
			   for($j=0;$j<32;$j++){
					$tq=array_shift(unpack("C",$tdata));
					if(($tq & 192)==192){
						if($compresspos<0){
							$tdata .=fread($fp,1);
							$data .=$tdata;
						}else{
							$tdata=substr($data,$compresspos,2);
						}
						$tq=array_shift(unpack("n*",$tdata));
						$compresspos=($tq & 0x3fff);
						$tdata=substr($data,$compresspos,1);
						continue;
					}

					if($tq==0){
						break;
					}elseif($compresspos>0){
						$tna[]=array_shift(unpack("a*",substr($data,$compresspos+1,$tq)));
						$compresspos +=$tq+1;
						$tdata=substr($data,$compresspos,1);
					}else{
						$tdata=fread($fp,$tq);
						$data .=$tdata;
						$tna[]=unpack("a*",$tdata);
						$tdata=fread($fp,1);
						$data .=$tdata;
					}
			   }
			   $tdata=fread($fp,10);
			   $data .=$tdata;
			   $tq=unpack("n*",$tdata);
			   $tdata=fread($fp,$tq[5]);
			   $data .=$tdata;
			   $tttl=array_shift(unpack("n*",$tq[4]));
			   $tna=implode($tna,".");
			   if($tq[1]===15 && $tq[2]===1){
				   $tpref=array_shift(unpack("n*",substr($tdata,0,2)));
				   $tdata=substr($tdata,2);
				   $compresspos=-1;
				   $tmdata=substr($tdata,0,1);
				   $j=1;
				   for($k=0;$k<32;$k++){
						$tq=array_shift(unpack("C",$tmdata));
						if(($tq & 192)==192){
							if($compresspos<0){
								$tmdata .=substr($tdata,$j,1);
								$j++;
							}else{
								$tmdata=substr($data,$compresspos,2);
							}
							$tq=array_shift(unpack("n*",$tmdata));
							$compresspos=($tq & 0x3fff);
							$tmdata=substr($data,$compresspos,1);
							continue;
						}
						if($tq==0){
							break;
						}elseif($compresspos>0){
							$tmx[]=array_shift(unpack("a*",substr($data,$compresspos+1,$tq)));
							$compresspos +=$tq+1;
							$tmdata=substr($data,$compresspos,1);
						}else{
							$tmdata=substr($tdata,$j,$tq);
							$j +=$tq;
							$tmx[]=array_shift(unpack("a*",$tmdata));
							$tmdata=substr($tdata,$j,1);
							$j++;
						}
				   }
				   
				   $rs['mx'][$tttl][]=implode($tmx,".");
			   }
		   }
		   arsort($rs,SORT_ASC);
		   foreach($rs['mx'] as $key=>$values){
			   arsort($values,SORT_ASC);
			   foreach($values as $value){
				  $mxs[]=$value;
			   }
		   }
		   fclose($fp);
		   return $mxs;
		}
	}
}

function sendemail($toemail,$subject,$message,$additional){
	global $M_db,$db_bbsname,$rg_name,$db_bbsurl,$windid,$winduid,$timestamp,$regpwd,$manager,$db_ceoemail,$fromemail,$pwd_user,$submit,$receiver,$old_title,$fid,$tid,$pwuser,$db_charset,$sendtoname;
	!$fromemail && $fromemail = $db_ceoemail;
	require_once GetLang('email');
	$lang[$subject]		&& $subject    = $lang[$subject];
	$lang[$message]		&& $message    = $lang[$message];
	$lang[$additional]	&& $additional = $lang[$additional];
	//$subject = Char_cv($subject);
	//$message = Char_cv($message);
	$subject = stripslashes($subject);
	$message = stripslashes($message);
	if ($M_db->S_method == 1){
		if (@mail($toemail,$subject,$message,$additional)){
			return 1;
		} else{
			return 0;
		}
	} elseif ($M_db->S_method == 2){
		if(!$fp=fsockopen($M_db->smtp['host'],$M_db->smtp['port'],$errno,$errstr)){
			return false;
		}
		if(substr(fgets($fp,512),0,3)!="220"){
			return false;
		}
		if($M_db->smtp['auth']) {
			fwrite($fp,"EHLO phpwind\r\n");
			while($rt=strtolower(fgets($fp,512))){
				if(strpos($rt,"-")!==3 || empty($rt)){
					break;
				}elseif(strpos($rt,"2")!==0){
					return false;
				}
			}
			fwrite($fp, "AUTH LOGIN \r\n");
			if(substr(fgets($fp,512),0,3) != 334){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['user'])." \r\n");
			if(substr(fgets($fp,512),0,3) != 334){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['pass'])." \r\n");
			if(substr(fgets($fp,512),0,3) != 235){
				return false;
			}
		} else {
			fwrite($fp, "HELO phpwind\r\n");
		}
		$from = $M_db->smtp['from'];
		$from = preg_replace("/.*\<(.+?)\>.*/", "\\1", $from);
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		if(substr(fgets($fp,512),0,3) != 250){
			return false;
		}
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		if(substr(fgets($fp,512),0,3) != 250){
			return false;
		}
		fwrite($fp, "DATA\r\n");
		if(substr(fgets($fp,512),0,3) != 354){
			return false;
		}
		$subject = str_replace("\n",' ',$subject);
		$msg  = "Date: ".Date("r")."\r\n";
		$msg .= "From: \"=?$db_charset?B?".base64_encode($windid)."?=\" <$fromemail>\r\n";
		$msg .= "To: \"=?$db_charset?B?".base64_encode($sendtoname)."?=\" <$toemail>\r\n";
		$msg .= "Subject: =?$db_charset?B?".base64_encode($subject)."?=\r\n";
		$msg .= "X-mailer: PHPWind mailer\r\n";
		$msg .= "Mime-Version: 1.0\r\n";
		$msg .= "Content-Type: text/plain;\r\n";
		$msg .= "\tcharset=\"$db_charset\"\r\n";
		$msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$msg .= chunk_split(base64_encode($message))."\r\n.\r\n";
		fwrite($fp, $msg);
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return true;
	} elseif ($M_db->S_method == 3){
		if(!$M_db->mailmx($toemail)){
			return false;
		}
		foreach($M_db->smtp['tomx'] as $server){
			if(($fp=fsockopen($server,$M_db->smtp['port'],$errno,$errstr)) && substr(fgets($fp,512),0,3)=="220"){
				break;
			}
		}
		fwrite($fp, "HELO ".$M_db->smtp['smtphelo']."\r\n");
		//echo "in";
		//echo fgets($fp,512);exit;
		if(substr(fgets($fp,512),0,3)!="250"){

			fwrite($fp,"EHLO ".$M_db->smtp['smtphelo']."\r\n");
			while($rt=strtolower(fgets($fp,512))){
				if(strpos($rt,"-")!==3 || empty($rt)){
					break;
				}elseif(strpos($rt,"2")!==0){
					return false;
				}
			}
			fwrite($fp, "AUTH LOGIN \r\n");
			if(substr(fgets($fp,512),0,3) != 334){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['user'])." \r\n");
			if(substr(fgets($fp,512),0,3) != 334){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['pass'])." \r\n");
			if(substr(fgets($fp,512),0,3) != 235){
				return false;
			}
		}
		//echo "in";
		$from = $M_db->smtp['smtpmxmailname'];
		//echo $from;exit;
		$reply= $M_db->smtp['from'];
		//$from = preg_replace("/.*\<(.+?)\>.*/", "\\1", $from);
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		if(substr(fgets($fp,512),0,3) != 250){
			return false;
		}
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		if(substr(fgets($fp,512),0,3) != 250){
			return false;
		}
		fwrite($fp, "DATA\r\n");
		if(substr(fgets($fp,512),0,3) != 354){
			return false;
		}
		$subject = str_replace("\n",' ',$subject);
		$msg  = "Date: ".Date("r")."\r\n";
		$msg .= "From: \"=?$db_charset?B?".base64_encode($windid)."?=\" <$from>\r\n";
		$msg .= "Reply-To: \"=?$db_charset?B?".base64_encode($windid)."?=\" <$fromemail>\r\n";
		$msg .= "To: \"=?$db_charset?B?".base64_encode($sendtoname)."?=\" <$toemail>\r\n";
		$msg .= "Subject: =?$db_charset?B?".base64_encode($subject)."?=\r\n";
		$msg .= "X-mailer: PHPWind mailer\r\n";
		$msg .= "Mime-Version: 1.0\r\n";
		$msg .= "Content-Type: text/plain;\r\n";
		$msg .= "\tcharset=\"$db_charset\"\r\n";
		$msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$msg .= chunk_split(base64_encode($message))."\r\n.\r\n";
		fwrite($fp, $msg);
		if(substr(fgets($fp,512),0,3) != 250){
			return false;
		}
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return true;
		//hacker
	} else {
		//hacker
	}
}

function pw_var_cv($array,$c=1,$t='',$var=''){
	$c && $var="array(\r\n";
	$t.="\t";
	if(is_array($array)){
		foreach($array as $key => $value){
			$var.="$t'".str_replace(array("\\","'"),array("\\\\","\'"),$key)."'=>";
			if(is_array($value)){
				$var.="array(\r\n";
				$var=pw_var_cv($value,0,$t,$var);
				$var.="$t),\r\n";
			} else{
				$var.="'".str_replace(array("\\","'"),array("\\\\","\'"),$value)."',\r\n";
			}
		}
	}
	if($c){
		$var.=")";
	}
	return $var;
}
?>