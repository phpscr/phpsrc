<?php
$wind_in='hp';
require_once('global.php');
require_once(R_P.'require/header.php');
!$faqjob && $faqjob=1;
if($faqjob==3){
	$helpdb=array();
	$query=$db->query("SELECT * FROM pw_help ORDER BY id");
	while($rt=$db->fetch_array($query)){
		$helpdb[]=$rt;
	}
}
require_once PrintEot('faq');footer();
?>