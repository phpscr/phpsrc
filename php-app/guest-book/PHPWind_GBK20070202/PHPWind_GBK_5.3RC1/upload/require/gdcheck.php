<?php
!function_exists('readover') && exit('Forbidden');
function GdCheck($filename,$offset,$keyword)
{
	global $db_olsize;
	if(!$offset || $offset%($db_olsize+1)!=0) return 0;
	$fp=fopen($filename,"rb");
	flock($fp,LOCK_SH);
	fseek($fp,$offset);
	$Checkdata=fread($fp,$db_olsize);
	fclose($fp);
	if(strpos("\n".$Checkdata,"\n".$keyword."\t")!==false){
		return $Checkdata;
	}else{
		return 0;
	}
}
function GdGetInsert($filename,$gdnum){
	global $db_olsize,$windid,$onlineip;
	$N_offset=0;
	$fp=fopen($filename,"rb");
	flock($fp,LOCK_SH);
	while(feof($fp)===false){
		$Checkdata=fread($fp,($db_olsize+1)*2000);
		if(ereg("online.php$",$filename) && $offset=strpos("\n".$Checkdata,"\n".$windid."\t")){
			$offset+=$N_offset;
			break;
		}elseif(ereg("guest.php$",$filename) && $offset=strpos("\n".$Checkdata,"\n".$onlineip."\t")){
			$offset+=$N_offset;
			break;
		}elseif(!$padfp && $padfp=strpos($Checkdata,str_pad(" ",$db_olsize-1)."\n")){
			$padfp+=$N_offset-1;
		}
		$N_offset=ftell($fp);
	}

	if($offset){
		
		fseek($fp,$offset,SEEK_SET);
		$Checkdata=fread($fp,$db_olsize);
		if($windid && strpos($Checkdata,"\t".$onlineip."\t")===false && $gdnum) $offset='';
		fclose($fp);
		//$windid 安全验证
	}else{
		fclose($fp);
		
		$offset=$padfp ? $padfp : $N_offset;
		global $timestamp,$winduid,$guestinbbs,$userinbbs;
		$acttime=get_date($timestamp,'m-d H:i');
		$fidwt=strlen($fid)>4 ? '' : (int)$fid;
		$tidwt=strlen($tid)>7 ? '' : (int)$tid;
		if($filename===D_P."data/bbscache/guest.php"){
			$newonline="$onlineip\t$timestamp\t<FiD>$fidwt\t$tidwt\tPost Check\t$acttime\t$gdnum\t";
			GdWriteInline($filename,str_pad($newonline,$db_olsize)."\n",$offset);
			include_once(D_P.'data/bbscache/olcache.php');
			$guestinbbs++;
			$olcache="<?php\n\$userinbbs=$userinbbs;\n\$guestinbbs=$guestinbbs;\n?>";
			writeover(D_P.'data/bbscache/olcache.php',$olcache);
		}
		$offset='';
	}
	return array($offset,$Checkdata);
}
function GdWriteInline($filename,$data,$offset)
{
	if(!$offset || $offset%($db_olsize+1)!=0) return 0;
	$fp=fopen($filename,"rb+");
	//flock($fp,LOCK_EX);
	fseek($fp,$offset);
	fwrite($fp,$data);
	fclose($fp);
}
function ConcleGd($offset,$gdnum='',$checknum=''){
	global $winduid,$windid,$db_olsize,$onlineip;
	if($winduid!=''){
		$feild=9;
		$keyword=$windid;
		$D_name="data/bbscache/online.php";
	}else{
		$feild=6;
		$keyword=$onlineip;
		$D_name="data/bbscache/guest.php";
	}
	if($Checkdata=GdCheck(D_P.$D_name,$offset,$keyword)){
		$data_array=explode("\t",$Checkdata);
		array_pop($data_array);
		$returngdnum=$data_array[$feild];
		if($gdnum || $checknum==$returngdnum){
			$data_array[$feild]=$gdnum;
			$newonline=str_pad(implode("\t",$data_array)."\t",$db_olsize)."\n";
			GdWriteInline(D_P.$D_name,$newonline,$offset);
		}
	} else{
		list($offset,$Checkdata)=GdGetInsert(D_P.$D_name,$gdnum);
		if($offset){
			$data_array=explode("\t",$Checkdata);
			array_pop($data_array);
			$returngdnum=$data_array[$feild];
			if($gdnum || $checknum==$returngdnum){//已验证 $checknum 非空
				$data_array[$feild]=$gdnum;
				$newonline=str_pad(implode("\t",$data_array)."\t",$db_olsize)."\n";
				GdWriteInline(D_P.$D_name,$newonline,$offset);
			}
		}else{
			$returngdnum=$gdnum;
		}
	}
	return $returngdnum;
}
?>