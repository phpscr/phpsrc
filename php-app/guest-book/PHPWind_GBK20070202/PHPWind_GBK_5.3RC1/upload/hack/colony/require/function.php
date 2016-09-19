<?php
function cn_credit(){
	global $cn_moneytype,$db_credits,$db_currencyname;
	$credits_array=explode("\t",$db_credits);
	$money_array=array("money","rvrc","credit","currency");
	$point_array=array(
		'money'		=>	"$credits_array[0]",
		'rvrc'		=>	"$credits_array[2]",
		'credit'	=>	"$credits_array[4]",
		'currency'	=>	"$db_currencyname"
	);
	$unit_array=array(
		'money'		=>	"$credits_array[1]",
		'rvrc'		=>	"$credits_array[3]",
		'credit'	=>	"$credits_array[5]",
		'currency'	=>	""		
	);
	if(in_array($cn_moneytype,$money_array)){
		return array($point_array[$cn_moneytype],$unit_array[$cn_moneytype]);
	}else {
		include(D_P."data/bbscache/creditdb.php");
		return array($_CREDITDB[$cn_moneytype][0],$_CREDITDB[$cn_moneytype][1]);
	}
}

function getphotourl($path){
	global $attachname;
	$patharray=geturl($path);
	return $patharray[0];
}

function getsmallurl($path){
	global $attachname;
	$filepath = $path;
	$path=$attachname."/".$path;
	$smallpath=substr($path,0,strrpos($path,"/")+1)."s_".substr($path,strrpos($path,"/")+1);
	if(file_exists($smallpath)){
		return $smallpath;
	}else{
		return getphotourl($filepath);
	}
}


function unlink_photo($path){
	global $attachdir;
	$a_url=geturl($path);
	if($a_url[1]=='Local'){
		P_unlink("$attachdir/$path");
	}elseif($a_url[1]=='Ftp'){
		require_once(R_P.'require/ftp.php');
		$ftp->delete($path);
		$ftp->close();
		unset($ftp);
	}
	$smallpic = getsmallurl($path);
	P_unlink($smallpic);
}

function cn_classcache(){
	global $db;
	$rs=$db->query("select * from pw_cnclass");
	$writemsg="<?php\n\$cnclassdb=array(\n";
	while ($cn=$db->fetch_array($rs)) {
		$writemsg.="\t'$cn[cid]'=>'$cn[cname]',\n";	
	}
	$writemsg.=");\n?>";
	writeover(D_P."data/bbscache/cn_class.php",$writemsg);
}

function colonylog($log){
	global $db;
	require GetLang('log');
	$log['username1']= Char_cv($log['username1']);
	$log['username2']= Char_cv($log['username2']);
	$log['field1']   = Char_cv($log['field1']);
	$log['field2']   = Char_cv($log['field2']);
	$log['field3']   = Char_cv($log['field3']);
	$log['descrip']  = Char_cv($lang[$log['descrip']]);
	$db->update("INSERT INTO pw_forumlog (type,username1,username2,field1,field2,field3,descrip,timestamp,ip) VALUES('$log[type]','$log[username1]','$log[username2]','$log[field1]','$log[field2]','$log[field3]','$log[descrip]','$log[timestamp]','$log[ip]')");
}


function dosql($uid,$method="get",$addmoney=0){ //获取,执行SQL语句 用户uid,执行类别,执行金额
	global $db,$cn_moneytype;
	$money_array=array("money","rvrc","credit","currency");
	if (in_array($cn_moneytype,$money_array)) {
		$table="pw_memberdata";
		$addtype=$cn_moneytype;
		$addsql="";
		$set="";
	}else{
		$table="pw_membercredit";
		$addtype="value";
		$addsql="AND cid='$cn_moneytype'";
		$set=",cid='$cn_moneytype'";
	}
	$query="SELECT $addtype FROM $table WHERE uid='$uid' $addsql";
	$rs=$db->get_one($query);
	if ($method=="get") {
		$cn_moneytype=="rvrc" && $rs[$addtype]=(int)($rs[$addtype]/10);
		return $rs[$addtype];
	}elseif ($method=="set"){
		$addmoney=(int)$addmoney; //过滤
		$cn_moneytype=="rvrc" && $addmoney*=10;
		if(!isset($rs[$addtype])){
			$query="INSERT INTO $table SET $addtype=$addmoney,uid='$uid' $set";
		}else{
			$query="UPDATE $table SET $addtype=$addtype+'$addmoney' WHERE uid='$uid' $addsql";
		}
		$db->query($query);
	}
}

function resize_image($oldimg='', $newimg='', $picwidth=0, $picheight=0, $quality=75) //默认质量为75
{
   if ( !trim($oldimg) || !trim($newimg) )
       return 0;
   if ( !is_file($oldimg) )
       return 0;
   if ( !$picwidth && !$picheight )
       return 0;
   if ( $picwidth < 0 || $picheight < 0 )
       return 0;
   if ( $quality < 1 )
       return 0;
   // Get the extend name of the old file
   $filename = $oldimg;
   if ( strstr($oldimg, "/") )
   {
       $filename = explode("/", $oldimg);
       $filename = $filename[(count($filename)-1)];
   }
   if ( !strstr($filename, ".") )
       return 0;
   $filename = explode(".", $filename);
   $extname = end($filename); //$filename[(sizeof($filename)-1)];
   $extname = strtolower($extname);
   
   $filename = $newimg;
   if ( strstr($newimg, "/") )
   {
       $filename = explode("/", $newimg);
       $filename = $filename[(count($filename)-1)];
   }
   if ( !strstr($filename, ".") ) return 0;
   if($extname=="tmp") 	$extname=end(explode(".",$newimg)); //default extend name
   $filename = explode(".", $filename);
   $nextname = $filename[(count($filename)-1)];
   $nextname = strtolower($nextname);
   // Select the format of the new image
   switch ( $extname )
   {
       case "jpg"    : $im = imagecreatefromjpeg($oldimg); break;
       case "jpeg"   : $im = imagecreatefromjpeg($oldimg); break;
       case "gif"    : $im = imagecreatefromgif($oldimg); break;
       case "png"    : $im = imagecreatefrompng($oldimg); break;
       default       : return 0; break;
   }

   $color = imagecolorallocate($im, 255, 255, 255);
   $filesize = getimagesize($oldimg);

   if ( $picwidth && !$picheight )
       $picheight = $filesize[1]*$picwidth/$filesize[0];
   else if ( !$picwidth && $picheight )
       $picwidth = $filesize[0]*$picheight/$filesize[1];

   $output = imagecreatetruecolor($picwidth, $picheight);
   imagecopyresampled($output, $im, 0, 0, 0, 0, $picwidth, $picheight, $filesize[0], $filesize[1]);

   switch ( $nextname )
   {
       case "jpg"    : $result = imagejpeg($output, $newimg , $quality); break;
       case "jpeg"    : $result = imagejpeg($output, $newimg , $quality); break;
       case "gif"    : $result = imagegif($output, $newimg); break;
       case "png"    : $result = imagepng($output, $newimg); break;
       default        : $result = 0; break;
   }

   if ( $result )
       return 1;
   else
       return 0;
}
?>