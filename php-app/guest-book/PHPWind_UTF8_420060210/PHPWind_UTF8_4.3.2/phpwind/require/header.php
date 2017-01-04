<?php
!function_exists('readover') && exit('Forbidden');

if(file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false){
	@include (D_P."data/style/$skin.php");
}elseif(file_exists(D_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false){
	@include (D_P."data/style/$db_defaultstyle.php");
}else{
	@include (D_P."data/style/wind.php");
}
$yeyestyle=='no' ? $i_table="bgcolor=$tablecolor" : $i_table='class=i_table';

$msgsound='';
if($groupid=='guest' && $db_regpopup=='1'){
	$head_pop='head_pop';
} else{
	unset($head_pop);
	if($winddb['newpm']==1){
		if($db_msgsound && $secondurl!='message.php'){
			$msgsound="<bgsound src='$imgpath/$stylepath/msg/msg.wav' border='0'>";
		}
	}
}
if($db_union){
	$db_union=explode("\t",stripslashes($db_union));
	$db_union[0] && $db_hackdb=array_merge($db_hackdb,unserialize($db_union[0]));
}
@require (PrintEot('header'));
?>