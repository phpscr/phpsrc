<?php
$mod=1;//��2Ϊ�ڶ���ģʽ 3Ϊ��3��ģʽ����������������֧�̶ֳ�����
switch($mod){
	case 1:$DIR='simple/index.php?';break;
	case 2:$DIR='simple/index.php/';break;
	case 3:$DIR='simple/';break;//RewriteRule ^(.*)/simple/([a-z0-9\_]+\.html)$ $1/simple/index.php?$2
}

/*
* PHPWind ��һ����ѿ�Դ�����������Ҫ֧���κη��þͿ���������ʹ�á�
* ����������������м�ֵ���벻Ҫ�޸Ĵ˴����ã����Ƕ�����ʾ���ĵĸ�л��
* �������ţ����ڶ��û���֧�֣�PHPWind �����ø��á�
* ��1�ر� PHPWind ���������
*/
$db_adclose=0;

define('SIMPLEDIR',__FILE__ ? substr(dirname(__FILE__),0,-7) : './..');
define('SIMPLE',1);
require_once(SIMPLEDIR.'/global.php'); 
include_once(D_P.'data/bbscache/forum_cache.php');
$db_bbsurl=substr($db_bbsurl,0,-7);

if(file_exists(R_P."data/style/$skin.php") && strpos($skin,'..')===false){
	@include Pcv(R_P."data/style/$skin.php");
}else{
	@include(R_P."data/style/wind.php");
}
$yeyestyle=='no' ? $i_table="bgcolor=$tablecolor" : $i_table='class=i_table';
$db_union && $db_union=explode("\t",stripslashes($db_union));
$R_URL=substr(($cutchar=strrchr($REQUEST_URI,'?')) ? substr($cutchar,1) : substr(strrchr($REQUEST_URI,'/'),1),0,-5);
if($R_URL){
	$R_URL_A=explode('_',$R_URL);
	$prog=substr($R_URL_A[0],0,1);
	$id=(int)substr($R_URL_A[0],1);
	$page=(int)$R_URL_A[1];
}else{
	$prog='';
}

switch($prog){
	case 'f':
		$fid =& $id;
		include_once(R_P.'simple/mod_thread.php');break;
	case 't':
		$tid =& $id;
		include_once(R_P.'simple/mod_read.php');break;
	default:
		include_once(R_P.'simple/mod_index.php');
}
Update_ol();
if($db){
	$qn=$db->query_num;
}
$db_obstart==1 ? $ft_gzip="Gzip enabled":$ft_gzip="Gzip disabled";
if ($db_footertime==1){
	$t_array=explode(' ',microtime());
	$totaltime=number_format(($t_array[0]+$t_array[1]-$P_S_T),6);
	$wind_spend="Time $totaltime second(s),query:$qn";
}
include PrintEot('simple_footer');
$output=str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
ob_end_clean();
$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
echo $output;
flush;
exit;

function PageDiv($count,$page,$numofpage,$url)
{
	global $tablecolor;
	if ($numofpage<=1){
		return ;
	}else{
		$pages="<a href=\"{$url}_1.html\"><< </a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++)
		{
			if($i<1) continue;
			$pages.=" <a href={$url}_$i.html>&nbsp;$i&nbsp;</a>";
		}
		$pages.="&nbsp;&nbsp;<b>$page</b>&nbsp;";
		if($page<$numofpage)
		{
			for($i=$page+1;$i<=$numofpage;$i++)
			{
				$pages.=" <a href={$url}_$i.html>&nbsp;$i&nbsp;</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$pages.=" <input type='text' size='2' style='height: 16px; border:1px solid $tablecolor' onkeydown=\"javascript: if(window.event.keyCode==13) window.location='$db_bbsurl/{$url}_'+this.value+'.html';\"> <a href=\"{$url}_$numofpage.html\"> >></a> &nbsp;Pages: (  $numofpage total )";
		return $pages;
	}
}
?>