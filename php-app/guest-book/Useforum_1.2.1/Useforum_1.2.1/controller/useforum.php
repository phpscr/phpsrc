<?php
/**
 * Useforum  Copyright (C) 2010-2013 基础控制器
 * 获取基本信息，执行基础任务。
 * 添加日期 13-6 GW
 */
class useforum extends spController{
	/**
	  * 构造函数
	  * 添加日期 13-6 GW
	  */
	function __construct(){
		//启动父类构造函数的操作
		parent::__construct();
		$option = spClass("lib_common") ->findAll();
		//循环输出全局变量
		foreach($option as $data){
			$this->$data['name'] = $data['val'];
		}
        $this->header = "<meta name=\"generator\" content=\"Useforum\" />".$this->header;
		//系统信息
		$this->useforumversion = "1.2.1";
		//获取编码当前页地址
		$currenturl = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];  
		$this->currenturl = rawurlencode($currenturl);
		//处理提醒
		$notice = spAccess('r' , $_SESSION["userinfo"]["uname"]);
		if ($notice["update"] ==1){
			$this->notice=$notice;
		}
		//存在自动登录cookie则写入session并设置权限
		if((!$_SESSION["userinfo"])&&($_COOKIE['autologin'])){
			$notice = spAccess('r',$_COOKIE['autologin']);
			//检测安全密钥
			if($notice['temppassword'] === $_COOKIE['temppassword']){
				$result = spClass('lib_user')->find(array('uname'=>$_COOKIE['autologin']));
				spClass('spAcl')->set($result['acl']); 
				$_SESSION["userinfo"] = $result;
			}
		}
		//输出导航栏
		$this->menus = spClass("lib_menu")->spLinker()->findAll(array("hidden" =>0),"`order` ASC");
		if(!isset($_SESSION['time'])){
			$_SESSION['time']=0;
		}
		//获取站点地址
		$site_uri = trim(dirname($GLOBALS['G_SP']['url']["url_path_base"]),"\/\\");
		if( '' == $site_uri ){
			$site_uri = 'http://'.$_SERVER["HTTP_HOST"];
		}else{
			$site_uri = 'http://'.$_SERVER["HTTP_HOST"].'/'.$site_uri;
		}
		$this->siteurl = $site_uri;
		//判断站点是否关闭，跳转至登录页面
		if($this->is_close == "1"&& $_SESSION['userinfo']['acl']!="GBADMIN" &&$_GET['a']!="login"){
			$this->jump(spUrl('main', 'login'));//无id按不存在处理
		}
		$_SESSION['userinfo'] = spClass("lib_user")->find(array('uname'=>$_SESSION['userinfo']['uname']));
	}

	/**
	 * 跳转函数
	 * @param msg 提示信息
	 * @param url 跳转地址
	 * @param time 等待时间
	 * 添加日期 13-7-21 GW
	 */
	public function success($msg, $url,$time=1){
		$this->msg = $msg;
		$this->url = $url;
		$this->time = $time;
		$this->type = "s";
		$this->display("jump.html");
		exit();
	}

	public function error($msg, $url,$time=1){
		$this->msg = $msg;
		$this->url = $url;
		$this->time = $time;
		$this->display("jump.html");
		exit();
	}

	/* 无权限提示及跳转*/
	public function acljump(){
		$this->msg = '您没有权限进行此操作！';
		$this->url = spUrl("main","login");
		$this->time = 1;
		$this->display("jump.html");
		exit();
	}

	/*生成验证码*/
	function code(){
		$vcode = spClass('xpClickCode');
		$vcode->display();
	}
}