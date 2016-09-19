<?php
/**
 * Useforum  Copyright (C) 2010-2013 论坛基础控制器
 * 添加日期 2011 GW
 */
class main extends useforum
{
	/*
	 * 首页
	 * 若需要设置独立主页，须为版块列表建立独立控制器，此处可随意调用数据。
	 * 技术支持 U.gw269.com
	 */
	public function index(){
		if($this->default_index == "0"){
			$this->forum();
			$this->display("main_forum.html");
		}else{
			$this->now();
			$this->display("main_now.html");
		}
	}

	//版块列表
	public function forum(){
		$this->results = spClass("lib_forum")->spLinker()->findAll(NULL,"`order` ASC");
		$this->user = spClass("lib_user")->findCount(); // 使用了findCount
		$this->topicnum = spClass("lib_topic")->findCount();
		$this->newer = spClass("lib_user")->find(null,"ctime DESC","uname");
	}

	// 此刻
	public function now(){
		$this->results = spClass("lib_feed")->spLinker()->spPager($this->spArgs("page",1),30)->findAll(null,"ctime DESC");
		$this->pager = spClass("lib_feed")->spPager()->getPager();
	}

	// 查看当前版块内容
	public function viewforum(){
		if( $forum = $this->spArgs("id") ){
			$this->info = spClass("lib_forum")->spLinker()->find(array('id'=>$forum));
			if(!$this->info)	$this->error("版块不存在！", spUrl('main', 'index'));
			$i =spClass("lib_user")->find(array('uid'=>$_SESSION["userinfo"]["uid"],'credits')) ;
			if( $i['credits'] < $this->info['authority'] && $_SESSION["userinfo"]["acl"] !='GBADMIN' &&  $_SESSION["userinfo"]["uname"] ) $this->error("本版块只有积分超过{$this->info['authority']}才可以浏览！您当前积分{$i['credits']}", spUrl('main', 'index'));
			$this->tops = spClass("lib_topic")->spLinker()->findAll(array('forum'=>$forum,'top'=>"1"),"rtime DESC");
			$this->results = spClass("lib_topic")->spLinker()->spPager($this->spArgs("page",1),$this->listpager)->findAll(array('forum'=>$forum,'top'=>"0"),"rtime DESC");
			$this->pager = spClass("lib_forum")->spPager()->getPager();
		}else{
			$this->error("版块不存在！", spUrl('main', 'index'));//无id按不存在处理
		}
	}

	// 查看内容
	public function view(){
		// 这里先判断是否传入了gid
		if( $gid = $this->spArgs("gid") ){
			$this->info = spClass("lib_topic")->spLinker()->find(array('gid'=>$gid),"gid,uname,title,ctime,ip,forum");
			if(!$this->info)	$this->error("话题不存在或已被删除！", "javascript:window.history.go(-1);");
			$i = spClass("lib_user")->find(array('uid'=>$_SESSION["userinfo"]["uid"],'credits')) ;
			if( $i['credits'] < $this->info['authority']  && $_SESSION["userinfo"]["acl"] !='GBADMIN' && $this->info['uname'] !=$_SESSION["userinfo"]["uname"] &&  $_SESSION["userinfo"]["uname"]) $this->error("本话题只有积分超过{$this->info['authority']}才可以浏览！您当前积分{$i['credits']}", spUrl('main', 'index'));
			spClass('lib_topic')->incrField(array( 'gid' =>$this->spArgs("gid")), 'view');
			$this->poster = spClass("lib_user")->find(array('uname'=>$this->info['uname']),"uid,forum,live,post,credits,acl,ctime,avatar");
			$this->results = spClass("lib_reply")->spLinker()->spPager($this->spArgs("page",1),$this->replypager)->findAll(array('gid'=>$gid),"ctime ASC","content,rid,ip,uname,ctime");
			$this->pager = spClass("lib_topic")->spPager()->getPager();
		}else{
			$this->jump("javascript:window.history.go(-1);");
		}
	}

	//编辑话题
	public function edit(){
		if( $gid = $this->spArgs("gid") ){
			$editObject = spClass("lib_topic");
			$unamer = $editObject->find(array('gid'=>$this->spArgs("gid")));
			if(!$unamer) $this->error("话题不存在或已被删除！", spUrl('main', 'index'));
			if ("GBADMIN" == $_SESSION["userinfo"]["acl"] || $unamer['uname'] == $_SESSION["userinfo"]["uname"] || $unamer['forum'] == $_SESSION["userinfo"]["forum"]){
				$this->info = $unamer;
			}else{
			$this->error('您无权编辑本话题', "javascript:window.history.go(-1);");
			}
			if(array_key_exists("contents",$this->spArgs())){
				$conditions = array('gid'=>$gid);
				$edit= strReplaces($this->spArgs());//正则过滤函数
				$results = $editObject->spVerifier($edit);
				if( false == $results ){
					$editObject->updateField($conditions, 'title', $edit['title'] );
					$editObject->updateField($conditions, 'authority', $this->spArgs('authority'));
					$time=date("Y-m-d H:i",$_SERVER['REQUEST_TIME']);
					if("GBADMIN" != $_SESSION["userinfo"]["acl"]){
						$edit['contents'] .="\n <br/>​<i>该话题由{$_SESSION["userinfo"]["uname"]}在{$time}编辑</i>";
					}
					$editObject->updateField($conditions, 'contents', $edit['contents'] );
					$this->success("编辑成功！", spUrl("main","view",$conditions));
				}else{
					foreach($results as $item){
						foreach($item as $msg){
							$this->errmsg = $msg;
						}
					}
				}
			}
		}else{
			$this->jump("javascript:window.history.go(-1);");
        }
	}

	//编辑评论
	public function redit(){
		if( $gid = $this->spArgs("rid") ){
			$unamer = spClass("lib_reply")->find(array('rid'=>$this->spArgs("rid")));
			$topic =  spClass("lib_topic")->find(array('gid'=>$unamer['gid']),"forum");
			if(!$unamer) $this->error("话题不存在或已被删除！", spUrl('main', 'index'));
			if ("GBADMIN" == $_SESSION["userinfo"]["acl"] || $unamer['uname'] == $_SESSION["userinfo"]["uname"] || $topic['forum'] == $_SESSION["userinfo"]["forum"]){
				$this->info = $unamer;
			}else{
				$this->error('您无权编辑本评论', "javascript:window.history.go(-1);");
			}
			if($content = $this->spArgs("content")){
				$conditions = array('rid'=>$this->spArgs('rid'));
				$edit= strReplaces($this->spArgs());//正则过滤函数
				$time=date("Y-m-d H:i",$_SERVER['REQUEST_TIME']);
				if("GBADMIN" != $_SESSION["userinfo"]["acl"]){
					$edit['content'] .= "\n <br/><i>该评论由{$_SESSION["userinfo"]["uname"]}在{$time}编辑</i>";
				}
				spClass("lib_reply")->updateField($conditions, 'content', $edit['content'] );
				$this->success("编辑成功！", spUrl("main","view",array("gid" =>$unamer['gid'])));
			}
		}else{
			$this->jump("javascript:window.history.go(-1);");
		}
	}

	//评分
	public function score(){
		if( $gid = $this->spArgs("gid") ){
			if(spClass('lib_score')->find(array( 'uname' => $_SESSION['userinfo']['uname'],'gid' => $gid)) ==null || $_SESSION['userinfo']['acl'] == "GBADMIN"){
				if($_SESSION['userinfo']['acl'] != "GBADMIN"){
					$results = spClass('lib_score')->spVerifier($this->spArgs());
				}else{
					$results = false;
				}
				if($results == false){
					if($_SESSION['userinfo']['uname'] != $this->spArgs("poster")){
						spClass('lib_user')->incrField(array('uname' => $this->spArgs("poster") ), 'credits',$this->spArgs("score"));
						$newrow = array( // PHP的数组
							'uname' => $_SESSION['userinfo']['uname'],
							'reason' => $this->spArgs("reason"),
							'ctime' => $_SERVER['REQUEST_TIME'],
							'score' => $this->spArgs("score"),
							'gid' => $gid
						);
						spClass('lib_score')->sendnotice($gid,$this->spArgs("score"));
						spClass('lib_score')->create($newrow);
						$this->success("评分成功！" , "javascript:window.history.go(-1);");
					}else{
						$this->error("您不能为自己的帖子评分！" , "javascript:window.history.go(-1);");
					}
				}else{
					foreach($results as $item){
						foreach($item as $msg){ 
							$this->error($msg,"javascript:window.history.go(-1);");
						}
					}
				}
			}else{
				$this->error("您已为该话题评过分了！" , "javascript:window.history.go(-1);");
			}
		}else{
			$this->jump("javascript:window.history.go(-1);");
        }
	}

	//搜索
	public function search(){
		if( $search = clearLabel($this->spArgs("search")) ){ //清空HTML标签
			if( "topic" == $this->spArgs("type") ){
				$search = spClass("lib_topic")->escape('%'. $search .'%' );
				$this->result1 = spClass("lib_topic")->spLinker()->findAll(" title like $search ","rtime DESC","title,uname,forum,ctime,rtime,gid,view"," 0,30 ");
			}elseif( "user" == $this->spArgs("type") ){
				$search = spClass("lib_user")->escape('%'. $search .'%' );
				$this->result2 = spClass("lib_user")->spLinker()->findAll(" uname like $search ","credits DESC","ctime,uname,uid,credits,introduce,live,avatar"," 0,30 ");
			}
		}
	}

	//高级搜索
	public function advanced_search(){
		$this->forumlist = spClass("lib_forum")->findAll(NULL,"`order` ASC","id,name");//获取版块
		$search = clearLabel($this->spArgs("search"));
		$search = spClass("lib_topic")->escape('%'. $search .'%' );
		$condition = " title like $search ";
		$author = spClass("lib_topic")->escape(clearLabel($this->spArgs("author")));
		if ( $author != '\'\''){
			$condition .= " AND  `uname` =  $author ";
		}
		$forum = $this->spArgs("forum") ;
		if ( $forum > 0 ){
			$condition .= " AND  `forum` = $forum  ";
		}
		if ($date = spClass("lib_topic")->escape(strtotime(clearLabel($this->spArgs("date"))))){
			$condition .= " AND  `ctime` < $date ";
		}
		$this->result1 = spClass("lib_topic")->spLinker()->findAll($condition,"rtime DESC","title,uname,ctime,rtime,forum,gid,view"," 0,30 ");
	}

	// 退出登录
	public function logout(){
		setcookie('autologin',"",time()-3600*24*10);
		setcookie('temppassword',"",time()-3600*24*10);
		// 这里是PHP.net关于删除SESSION的方法
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', $_SERVER['REQUEST_TIME']-42000, '/');
		session_destroy();
		// 跳转回首页
		$this->success("已退出，返回首页！", spUrl("main","index"));
	}

	// 登录
	public function login(){
		$userObj = spClass("lib_user"); // 实例化lib_user类
		if(array_key_exists("uname",$this->spArgs())){ // 已经提交，这里开始进行登录验证
			$uname = $this->spArgs("uname");
			$upass = md5($this->spArgs("upass"));
			$autologin = $this->spArgs("autologin");
			if( $_COOKIE['lognum'] <= 5 ||($_SERVER['REQUEST_TIME'] - $_COOKIE['logtime']) > 900){
				if( false == $userObj->userlogin($uname, $upass,$autologin) ){
					setcookie("lognum",$_COOKIE['lognum'] + 1, $_SERVER['REQUEST_TIME']+900);
					$_COOKIE['lognum']++;
					if($_COOKIE['lognum'] > 5)setcookie("logtime",$_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME']+3600);
					$this->errmsg ="用户名或密码错误，请重新输入！";
				}else{
					$userObj->last($uname);
					$ip = $userObj->setIP($uname);
					$city = getCity(array( "ip" => $ip ));
					$useracl = spClass("spAcl")->get(); // 通过acl的get可以获取到当前用户的角色标识
					$acl = "GBADMIN" == $useracl ? "管理员":"用户";
					if ( $ip ) $lastip = "<br>上次登录IP {$ip} 地点 {$city}";
					$i = spAccess("r",$uname);
					$i['last'] = $_SERVER['REQUEST_TIME'];
					spAccess("w",$uname,$i);
					$this->success("登录成功，欢迎您，尊敬的{$acl}！{$lastip}",rawurldecode($this->spArgs("backurl")),2);
				}
			}else{
				$this->errmsg ="输入错误超过5次，请等待15分钟再试！";
			}
		}
	}

	// 发布话题
	public function post(){
		$this->results = spClass("lib_forum")->findAll(NULL,"`order` ASC","id,name");//获取版块
		$this->fid = $this->spArgs("fid");
		if(array_key_exists("title",$this->spArgs())){//此处已经提交
			$post = strReplaces($this->spArgs());
			$guestbookObj = spClass("lib_topic");
			$results = $guestbookObj->spVerifier($post);
			if( false == $results ){
				if( ($_SERVER['REQUEST_TIME'] - $_SESSION['time']) > $this->posttime){
					$gid = $guestbookObj->create($post);
					$guestbookObj->sendAt($this->spArgs("contents"),$gid);
					$_SESSION['time']= $_SERVER['REQUEST_TIME'] ;
					spClass('lib_user')->incrField(array( 'uname' => $_SESSION["userinfo"]['uname'] ), 'post');
					spClass('lib_user')->incrField(array( 'uname' => $_SESSION["userinfo"]['uname'] ), 'credits',$this->newtopic);
					sendFeed(cutString($post['contents'],200),$url = spUrl("main","view",array('gid'=>$gid))); //发送动态
					$this->success("创建新话题成功！", $url);
				}else{
					$this->errmsg = "请不要灌水，请等待{$this->posttime}秒再发布！";
				}
			}else{
				// $results不是false，所以没有通过验证，错误信息是$results
				foreach($results as $item){
					// 每一个规则，都有可能返回多个错误信息，循环$item来获取多个信息
					foreach($item as $msg){
						$this->errmsg= $msg;
					}
				}
			}
		}
	}
	
	// 评论
	public function rpost(){
		if($this->spArgs('content')){
			// 已经提交，开始对数据进行验证
			$rObj = spClass("lib_reply"); // 实例化留言对象
			$reply= strReplaces($this->spArgs());//正则过滤
			// 这里直接验证全部的提交数据（$this->spArgs()获取全部提交数据）
			if( ($_SERVER['REQUEST_TIME'] - $_SESSION['time']) > $this->posttime){
				$rObj->sendnotice($reply['gid']);
				$rid = $rObj->create($reply);
				$rObj->sendAt($reply['content'],$rid,$reply['gid']);
				$_SESSION['time']= $_SERVER['REQUEST_TIME'] ;
				//增加积分以及发帖数
				spClass('lib_user')->incrField(array( 'uname' => $_SESSION["userinfo"]['uname'] ), 'post');
				spClass('lib_user')->incrField(array( 'uname' => $_SESSION["userinfo"]['uname'] ), 'credits',$this->newreply);
				spClass("lib_topic")->updateField(array( 'gid' => $this->spArgs("gid") ), 'rtime', $_SERVER['REQUEST_TIME']);
				$this->add = spClass("lib_reply")->spLinker()->find(array('rid'=>$rid),"ctime ASC","content,uname,ctime");
				$this->num = $rObj->findCount(array("gid"=>$this->spArgs('gid')));
				sendFeed(cutString($reply['content'],200),$url =
					spUrl("main","view",array('gid'=>$reply['gid'],'page' =>ceil($this->num/$this->replypager)))); //发送动态
				$this->display('main_rpost.html');
			}else{
				echo json_encode( array('message' => "请不要灌水，请等待{$this->posttime}秒再发布！"));
			}
		}else{
			echo json_encode( array('message' => "内容不能为空。"));//0.3.1的ajax评论需要单独验证
		}
	}

	//附件上传处理
	public function upload(){
		//文件保存目录路径
		$php_path = dirname(__FILE__) . '/';
		//文件保存目录路径
		$save_path = $php_path . 'attachment/';
		//定义允许上传的文件扩展名
		$ext_arr = array(
			'image' => explode(",",$this->allow_image_types),
			'file' => explode(",",$this->allow_file_types),
		);
		//最大文件大小
		$max_size = 1024*$this->max_upload_size;

		$save_path = realpath($save_path) . '/';

		//PHP上传失败
		if (!empty($_FILES['imgFile']['error'])) {
			switch($_FILES['imgFile']['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '文件只有部分被上传。';
					break;
				case '4':
					$error = '请选择文件。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = '上传超时。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			$this->alert($error);
		}

		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//文件的MINE类型
			//检查文件名
			if (!$file_name) {
				$this->alert("请选择文件。");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert("上传失败。");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				$this->alert("上传文件大小超过限制。");
			}
			//检查目录名
			$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
			if (empty($ext_arr[$dir_name])) {
				$this->alert("目录名不正确。");
			}
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
			}
			//创建文件夹
			if ($dir_name !== '') {
				$save_path .= $dir_name . "/";
				$save_url = $dir_name . "/";
				if (!file_exists($save_path)) {
					mkdir($save_path);
				}
			}
			$ymd = date("Ym");
			$save_path .= $ymd . "/";
			$save_url .= $ymd . "/";
			if (!file_exists('attachment' .$save_path)) {//BY:ZY/相对地址指明不清
				mkdir('attachment' .$save_path);
			}
			//新文件名
			$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			//移动文件
			$file_path = 'attachment' .$save_path. $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) == false) {
				$this->alert($save_path."上传文件失败。。。".$file_path);
			}
			@chmod($file_path, 0644);
			$file_url ='attachment/' . $save_url . $new_file_name;
			$db = spClass('lib_attachment');
			$condition = array(
				'name' => $file_name,
				'url' =>$file_url,
				'uid' =>$_SESSION["userinfo"]["uid"],
				'size' =>$file_size,
				'type' =>$_FILES['imgFile']['type'] ,
				'ctime' =>$_SERVER['REQUEST_TIME'],
			);
			$aid = $db -> create($condition);
			header('Content-type: text/html; charset=UTF-8');
			$json = spClass("Services_JSON");
			$url = spUrl('main','attachment',array('aid'=>$aid));
			echo $json->encode(array('error' => 0, 'url' => $url ,'title' => $file_name));
			exit;
		}
	}

	//上传附件提示框
	function alert($msg) {
		header('Content-type: text/html; charset=UTF-8');
		$json = spClass("Services_JSON");
		echo $json->encode(array('error' => 1, 'message' => $msg));
		exit;
	}

	/*
	 * 显示附件
	 * 添加日期 2013-08-28 ZY
	 */
	public function attachment(){
		if($this->domain_attachment && $_SERVER['HTTP_REFERER']):
			$domain = explode(",",$this->domain_attachment);  //1.1版本允许多个地址，半角逗号分隔
			foreach ($domain as &$d) {
				if(strpos($_SERVER['HTTP_REFERER'], $d) !== FALSE) :
					$result = true;
					break;
				endif;
			}
			if($result != true ) {
				header('Content-type: image/png');
				readfile('./template/common/link.png');//输出防盗链图片
				exit;
			}
		endif;
		$file =  spClass('lib_attachment')->find(array('aid'=>$this->spArgs('aid')),null,'url,type,size,name');
		if(substr($file['type'],0,5) == 'image'){
			header('Content-type: '.$file['type']);
		}else{
			header("Content-type: application/octet-stream");//代表非浏览器执行的东西，就是调用下载了
		}
		header("Accept-Length: ".$file['size']);//输出大小
		header("Content-Disposition: filename=".$file['name']);//输出文件名
		readfile('./'.$file['url']);//输出内容
		if(!$file['name']){ //不存在图片时
			header('Content-type: image/png');
			readfile('./template/common/noimg.png');
			exit;
		}
	}


}