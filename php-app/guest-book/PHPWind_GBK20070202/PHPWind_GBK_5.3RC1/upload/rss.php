<?php
error_reporting(0);
define('D_P',__FILE__ ? dirname(__FILE__).'/' : './');

$fid=(int)$_GET['fid'];
$Rss_newnum=20;
$Rss_listnum=20;
$Rss_updatetime=10;
$cache_path=D_P.'data/bbscache/rss_'.$fid.'_cache.xml';

if(time()-@filemtime($cache_path) > $Rss_updatetime*60){

	require_once('global.php');
	require_once(R_P.'require/rss.php');
	require_once(D_P.'data/bbscache/forum_cache.php');

	if($fid){
		$rt=$db->get_one("SELECT allowvisit,f_type FROM pw_forums WHERE fid='$fid'");
		if($rt['allowvisit'] != '' || $rt['f_type'] == 'hidden'){
			echo"<META HTTP-EQUIV='Refresh' CONTENT='0; URL=rss.php'>";exit;
		}
	}

	if($fid){
		$description="Latest $Rss_newnum article of ".$forum[$fid]['name'];
		$sql="WHERE t.fid='$fid' AND ifcheck=1 ORDER BY postdate DESC LIMIT $Rss_listnum";
	} else{
		$fids=$extra='';
		$query=$db->query("SELECT fid FROM pw_forums WHERE allowvisit='' AND f_type!='hidden'");
		while($rt=$db->fetch_array($query)){
			$fids.=$extra."'".$rt['fid']."'";
			$extra=',';
		}

		$description="Latest $Rss_newnum article of all forums";
		$sql="WHERE fid IN($fids) AND ifcheck=1 ORDER BY postdate DESC LIMIT $Rss_newnum";
	}

	$channel=array(
		'title'			=>  $db_bbsname,
		'link'			=>  $db_bbsurl,
		'description'	=>  $description,
		'copyright'		=>  "Copyright(C) $db_bbsname",
		'generator'		=>  "PHPWind BLOG by PHPWind Studio",
		'lastBuildDate' =>  date('r'),
	);

	$image = array(
		'url'		  =>  "$imgpath/rss.gif",
		'title'		  =>  'PHPWind Board',
		'link'		  =>  $db_bbsurl,
		'description' =>  $db_bbsname,
	);
	$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
	$Rss->channel($channel);
	$Rss->image($image);

	$query=$db->query("SELECT t.tid,t.fid,t.subject,t.author,t.postdate,tm.content FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid $sql");
	while($rt=$db->fetch_array($query)){
		$rt['content']=preg_replace("/\[post\](.+?)\[\/post\]/is","",$rt['content']);
		$rt['content']=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is","",$rt['content']);
		$rt['content']=preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is","",$rt['content']);
		$rt['content']=substrs($rt['content'],300);
		$item = array(
			'title'       =>  $rt['subject'],
			'description' =>  $rt['content'],
			'link'        =>  "$db_bbsurl/read.php?tid=$rt[tid]",
			'author'      =>  $rt['author'],
			'category'    =>  $forum[$rt['fid']]['name'],
			'pubdate'     =>  date('r',$rt['postdate']),
		);
		$Rss->item($item);
	}

	$Rss->generate($cache_path);
}
header("Content-type: application/xml");
@readfile($cache_path);
exit;
?>