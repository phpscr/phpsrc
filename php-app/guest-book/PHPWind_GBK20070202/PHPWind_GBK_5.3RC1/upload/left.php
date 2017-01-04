<?php
require_once('global.php');
include_once(D_P.'data/bbscache/forum_cache.php');

if(file_exists(R_P."data/style/$skin.php") && strpos($skin,'..')===false){
	@include Pcv(R_P."data/style/$skin.php");
}elseif(file_exists(R_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false){
	@include Pcv(R_P."data/style/$db_defaultstyle.php");
}else{
	@include (R_P."data/style/wind.php");
}

$catedb=$forumdb=array();
foreach($forum as $key=>$value){
	if($value['type']=='category'){
		$catedb[$key]=$value;
	}elseif($value['type']=='forum' && ($value['f_type']!='hidden' || $groupid=='3')){
		$forumdb[$value['fup']][$key]=$value;
	}
}
require_once PrintEot('left');
?>