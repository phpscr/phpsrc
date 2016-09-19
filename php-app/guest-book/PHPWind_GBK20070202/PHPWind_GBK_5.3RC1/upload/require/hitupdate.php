<?php
!function_exists('readover') && exit('Forbidden');

if(file_exists(D_P."data/bbscache/hits.txt")){
	if($hitsize<5120){
		$hitarray=explode("\t",readover(D_P."data/bbscache/hits.txt"));
		P_unlink(D_P."data/bbscache/hits.txt");
		$hits=array_count_values($hitarray);
		$count=0;
		foreach($hits as $key=>$value){
			if($key){
				$db->update("UPDATE pw_threads SET hits=hits+'$value' WHERE tid='$key'");
			}
			$count++;
			if($count>300) break;
		}
		$nowtime=($timestamp-$tdtime)/3600;
		$hit_control=floor($nowtime/$db_hithour)+1;
		if($hit_control>24/$db_hithour)$hit_control=1;
		$db->update("UPDATE pw_bbsinfo SET hit_control='$hit_control',hit_tdtime='$tdtime' WHERE id=1");
		unset($hitarray,$hits);
	}else{
		P_unlink(D_P."data/bbscache/hits.txt");
	}
}
?>