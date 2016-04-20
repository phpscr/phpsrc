<?php
//header("Content-type: text/html; charset=UTF-8"); 
     session_start(); 
     if (empty($page)) {$page=1;}
     if (isset($_GET['page'])==TRUE) {$page=$_GET['page']; }
 ?> 
 <html> 
 <head> 
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />  
 <title>Read Result</title> 
 <style type="text/css"> 
 <!-- 
.STYLE1 {font-size: 12px} 
 .STYLE2 {font-size: 18px} 
 --> 
 </style> 
 </head> 
 <body> 
 <table width="100%"  bgcolor="#CCCCCC"> 
 <tr> 
 <td > 
 <?php 
 if($page){ 
 $counter=file_get_contents("example.txt"); //读取txt文件内容到$counter
 $length=strlen($counter); 
 var_dump( $length);
 $page_count=ceil($length/5000); 
	echo "<hr>";
 var_dump($page_count);
 function msubstr($str,$start,$len){ 
     $strlength=$start+$len; 
     $tmpstr="";
     for($i=0;$i<$strlength;$i++) { 
     if(ord(substr($str,$i,1))==0x0a) { 
         $tmpstr.='<br />';
     }
     if(ord(substr($str,$i,1))>0xa0) { 
         $tmpstr.=substr($str,$i,2); 
         $i++; 
     }
     else{ 
         $tmpstr.=substr($str,$i,1); } 
     } 
     return $tmpstr; 
 } 
 //------------截取中文字符串---------
$c=msubstr($counter,0,($page-1)*5000); 
 $c1=msubstr($counter,0,$page*5000); 
 echo substr($c1,strlen($c),strlen($c1)-strlen($c)); 
 }?> 
 </td> 
 </tr> 
 </table> 
 <table width="100%"  bgcolor="#cccccc"> 
 <tr> 
 <td width="42%" align="center" valign="middle"><span class="STYLE1"> <?php echo $page;?> / <?php echo $page_count;?> 页 </span></td> 
 <td width="58%" height="28" align="left" valign="middle">
 <span class="STYLE1">
 <?php
 echo "<a href=m.php?page=1>首页</a> ";  
 if($page!=1){ 
     echo "<a href=m.php?page=".($page-1).">上一页</a> "; 
 } 
 if($page<$page_count){ 
     echo "<a href=m.php?page=".($page+1).">下一页</a> "; 
 }
 echo "<a href=m.php?page=".$page_count.">尾页</a>";  
 ?> 
 </span> </td> 
 </tr> 
 </table> 
 </body> 
 </html>
