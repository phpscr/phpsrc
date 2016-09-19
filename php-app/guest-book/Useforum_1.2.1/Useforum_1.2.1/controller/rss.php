<?php
/**
 * Useforum  Copyright (C) 2010-2013 信息源控制器
 * 添加日期 13-7-15 GW
 */
class rss extends useforum
{
	public function index(){
		$rss = new UniversalFeedCreator();
        $rss->useCached();
        $rss->title = $this->sitename;
        $rss->description = $this->description;
        $rss->link = $this->siteurl;
        $results =spClass("lib_topic")->findAll(null,"rtime DESC","title,uname,gid,contents","1,10");
        foreach ($results as $result) {
			$content=cutString($result['contents'],500);
			$item = new FeedItem();
			$item->title = cutString($result['title'],33);
			$item->author = $result['uname'];			
			$item->link = "{$this->siteurl}/index.php?c=main&a=view&gid={$result['gid']}";
			$item->description = "{$content}……";
			$rss->addItem($item);
		}
		$rss->saveFeed("RSS2.0", "./rss.xml");
    } 
	public function forum(){
		if( $forum = $this->spArgs("id") ){
			 $rss = new UniversalFeedCreator();
			 	$info=spClass("lib_forum")->find(array('id'=>$this->spArgs("id")),"name");
				$rss->useCached();
				$rss->title = "{$info['name']}-{$this->sitename}";
				$rss->description = $this->description;
				$rss->link = $this->siteurl;
				$results =spClass("lib_topic")->findAll(array('forum'=>$this->spArgs("id")),"rtime DESC","title,uname,gid,contents","1,10");
				foreach ($results as $result) {
					$content=cutString($result['contents'],500);
					$item = new FeedItem();
					$item->title = cutString($result['title'],33);
					$item->link = "{$this->siteurl}/index.php?c=main&a=view&gid={$result['gid']}";
					$item->author = $result['uname'];
					$item->description = "{$content}……";
					$rss->addItem($item);
				}
				$rss->saveFeed("RSS2.0", "./rss.xml");
		}else{
			// 无gid则直接跳转回首页
			$this->jump(spUrl("rss","index"));
		}
    }
	
}	