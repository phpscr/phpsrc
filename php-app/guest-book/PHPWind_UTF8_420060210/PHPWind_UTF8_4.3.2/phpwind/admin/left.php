<?php
!function_exists('adminmsg') && exit('Forbidden');
require GetLang('left');
$leftdb=$lang;
unset($lang);
$leftinfo='';
$i=3;

$imgtype=$styletype=array();
list($imgtype[a0],$styletype[a0])=GetDeploy('a0');
list($imgtype[a1],$styletype[a1])=GetDeploy('a1');

foreach($leftdb as $key=>$left){
	$id='a'.$i;
	list($imgname,$style)=GetDeploy($id);
	
	$left && $output1="<tr><td>
	<table width=98% align=center cellspacing=1 cellpadding=4 class=i_table>
		<tr><td class=head height=18><a style=\"float:right\" href=\"#\" onclick=\"return IndexDeploy('$id',1)\"><img id=\"img_$id\" src=\"$imgpath/wind/cate_$imgname.gif\" border=0 alt='open'></a>
		<b>$key</b></td></tr>
		<tbody id=\"cate_$id\" style=\"$style\">
		<tr><td class=b style=\"line-height:120%\">";
	$output2='';
	foreach($left as $key=>$value){
		if($rightset[$key]){
			if(is_array($value)){
				foreach($value as $k=>$v){
					$output2 .= $v;
				}
			}else{
				$output2 .= $value;
			}
		}
	}
	if($output2){
		$output1 .= $output2."</td></tr></tbody></table></td></tr>";
	}else{
		unset($output1);
	}
	$leftinfo .= $output1;
	$i++;
}

function GetDeploy($name){
	global $_COOKIE;
	if(strpos($_COOKIE['deploy'],"\t".$name."\t")===false){
		$type='fold';
	}else{
		$type='open';
		$style='display:none;';
	}
	return array($type,$style);
}
include PrintEot('adminleft');exit;
?>