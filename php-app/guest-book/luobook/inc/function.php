<? 
//获取传递参数
function getvar($var){
	$result = isset($_GET[$var])?$_GET[$var]:$_POST[$var];
	$result = addslashes(trim($result));
	return $result;
}
//过滤字符
function Text2Html($txt){
        $txt = str_replace("  ","　",$txt);
        $txt = str_replace("<","&lt;",$txt);
        $txt = str_replace(">","&gt;",$txt);
        $txt = preg_replace("/[\r\n]{1,}/isU","<br/>\r\n",$txt);
        return $txt;
}
//留言表单
function book_form(){
	$var_temp="<div class='block'>
    	<div class='b_list'>
            <ul>
              <li>
                <div class='title'>
                  <div class='name'>我要留言</div>
                  <div class='time'></div>
                        <div class='clear'></div>
                </div>
                    <div class='content'>
                   	  <form name='form1' action='index.php?act=add' method='post' id='form1' onsubmit='return check();'>
                      <div class='ly_list'>
                      		<ul>
                            	<li><div class='left'>昵称：</div><div class='right'><input name='name' type='text' class='text' /> *</div></li>
                                <li><div class='left'>Emial：</div><div class='right'><input name='email' type='text' class='text' /></div></li>
                                <li><div class='left'>内容：</div><div class='right'><textarea name='content' id='textarea' cols='45' rows='5' class='textarea'></textarea> *</div></li>
                                <li><div class='left'></div><div class='right'>
                                  <input type='submit' name='button' id='button' value='提交' class='btn' />
                                </div></li>
                            </ul>
                      </div>
                      </form>
                </div>
					
              </li>
            </ul>
            <div class='clear'></div>
        </div>
    </div>";
	echo $var_temp;
}
//留言列表
function book_list(){
	global $db;
	$page 			= getvar('page') ? getvar('page') : 1;
	$page_size 		= 10;
	$sqlstr="select * from luo_booklist  where state=1 order by id desc";
	$total_nums = $db->getRowsNum ( $sqlstr );
	$news_list = $db->selectLimit ( $sqlstr, $page_size,($page - 1) * $page_size );
	
	echo "<div class='block'><div class='b_list'><ul>";
	foreach ($news_list as $list){
         echo "<li><div class='title'><div class='email'><a href='mailto:".$list['email']."'>邮箱</a></div><div class='name'>".$list['name']."</div><div class='time'>于 ".$list['addtime']." 发表留言：</div><div class='clear'></div></div><div class='content'>".$list['content'];
               if (strlen($list['replay'])>0){
					echo "<div class='reply'><div class='gly'><b>管理员回复：</b>".$list['replaytime']."</div><div class='gly_content'>".$list['replay']."</div></div>";
					}
            	echo "</div></li>";
			} 
            echo "</ul><div class='clear'></div></div></div><div class='pagelist'>".pagelist($sqlstr,$page_size,"index.php?act=view&",$page)."</div>";
}
//管理列表
function manage_book_list(){
	global $db;
	$page 			= getvar('page') ? getvar('page') : 1;
	$page_size 		= 10;
	$sqlstr="select * from luo_booklist order by id desc";
	$total_nums = $db->getRowsNum ( $sqlstr );
	$news_list = $db->selectLimit ( $sqlstr, $page_size,($page - 1) * $page_size );
	
	
	echo "<div class='block'><div class='b_list'><ul>";
	
	
	foreach ($news_list as $list){
         echo "<li><div class='title'><div class='email'><a href='mailto:".$list['email']."'>邮箱</a> | <a href='index.php?act=replay&id=".$list['id']."'>回复</a> | <a href='manage_ok.php?act=check&id=".$list['id']."'>".getstate($list['id'])."</a> | <a href='manage_ok.php?act=del&id=".$list['id']."' onClick='javascript:return confirm(\"确实要删除吗?\")'>删除</a></div><div class='name'>".$list['name']."</div><div class='time'>于 ".$list['addtime']." 发表留言：</div><div class='clear'></div></div><div class='content'>".$list['content'];
               if (strlen($list['replay'])>0){
					echo "<div class='reply'><div class='gly'><b>管理员回复：</b>".$list['replaytime']."</div><div class='gly_content'>".$list['replay']."</div></div>";
					}
            	echo "</div></li>";
			} 
            echo "</ul><div class='clear'></div></div></div><div class='pagelist'>".pagelist($sqlstr,$page_size,"index.php?act=login&",$page)."</div>";
}
//默认首页
function indexx(){
	global $db;
	$act = Text2Html(getvar('act'));
	$name = Text2Html(getvar('name'));
	$email = Text2Html(getvar('email'));
	$content = Text2Html(getvar('content'));
	
	if($act=='add'){//添加留言
	$record = array(
			'name'		=>$name,
			'email'		=>$email,		
			'content'		=>$content,
			'addtime'		=>date ( "Y-m-d H:i:s" )
		);
		$id = $db->insert('luo_booklist',$record);
		echo "<script>alert('留言成功');location.href='index.php';</script>";
	}

	book_form();//留言表单
	book_list();//留言列表
}
//登录界面
function loginx(){
	$var_temp="<div class='block'>
    	<div class='b_list'>
            <ul>
              <li>
                <div class='title'>
                  <div class='name'>管理登录</div>
                  <div class='time'></div>
                        <div class='clear'></div>
                </div>
                    <div class='content'>
                   	  <form name='form1' action='index.php?act=manage' method='post' id='form1' onsubmit='return checklogin();'>
                      <div class='ly_list'>
                      		<ul>
                            	<li>
                            	  <div class='left'>用户名：</div>
                            	  <div class='right'><input name='username' type='text' class='text' /></div></li>
                                <li>
                                  <div class='left'>密　码：</div>
                                  <div class='right'><input name='password' type='password' class='text' /></div></li>
                                <li><div class='left'></div><div class='right'>
                                  <input type='submit' name='button' id='button' value='提交' class='btn' />
                                </div></li>
                            </ul>
                      </div>
                      </form>
                </div>
					
              </li>
            </ul>
            <div class='clear'></div>
        </div>
    </div>
<div class='clear'></div></div>";
	echo $var_temp;
}

//登录
function login() {
	global $db;
	 if (isset ( $_POST ["username"] )) {
			$username = $_POST ["username"];
		} else {
			$username = "";
		}
		if (isset ( $_POST ["password"] )) {
			$password = $_POST ["password"];
		} else {
			$password = "";
		}
		//记住用户名
		//setcookie (username, $username,time()+3600*24*365);
		if (empty($username)||empty($password)){
			exit("<script>alert('用户名或密码不能为空！');window.history.go(-1)</script>");
		}
		$user_row = $db->getOneRow("select * from luo_manage where username='".$username."' and password='".md5($password)."'");

		if (!empty($user_row )) {
			$_SESSION['username'] = $user_row['username']; 
			$_SESSION['password'] = $user_row['password'];
			$_SESSION['id'] = $user_row['id']; 
			echo '<script>top.location="index.php?act=login";</script>';
		}else{
			exit("<script>alert('用户名或密码不正确！');window.history.go(-1)</script>");
		}
}
//留言状态
function getstate($id){
	global $db;
	$get_row = $db->getOneRow("select * from luo_booklist where id='".$id."'");
	switch ($get_row['state']){
		case "1":
			$var_state= "已审核";
			break;
		case "0":
			$var_state= "未审核";
			break;
	}
	return $var_state;
}
//退出
function quit() {
	session_unset();
	session_destroy();
	echo '<script>location="index.php";</script>';
}
//删除
function del($id){
	global $db;
	$db->delete('luo_booklist',"id=".$id);
	header("Location: index.php?act=login");
}
//审核
function check($id){
	global $db;
	$get_row = $db->getOneRow("select * from luo_booklist where id='".$id."'");
	switch ($get_row['state']){
		case "1":
			$var_state= 0;
			break;
		case "0":
			$var_state= 1;
			break;
	}
	$record = array(
		'state'			=>$var_state,
		'replaytime'	=>date ( "Y-m-d H:i:s" )
	);
	
	$db->update('luo_booklist',$record,'id='.$id);
	header("Location: index.php?act=login");
}
//回复
function replay($id){
	global $db;
	$sqlstr="select * from luo_booklist where id=".$id;
	$list = $db->getOneRow($sqlstr);
	$var_temp = "<div class='block'><div class='b_list'><ul><li><div class='title'><div class='name'>回复留言</div><div class='time'></div><div class='clear'></div></div><div class='content'><form name='form1' action='manage_ok.php?act=addreplay' method='post' id='form1' onsubmit='return check();'><div class='ly_list'><ul><li><div class='left'>昵称：</div><div class='right'><input name='name' type='text' class='text' value='".$list['name']."' /> *</div></li><li><div class='left'>Emial：</div><div class='right'><input name='email' type='text' class='text' value='".$list['email']."' /></div></li><li><div class='left'>内容：</div><div class='right'><textarea name='content' id='textarea' cols='45' rows='5' class='textarea'>".$list['content']."</textarea>*</div></li><li><div class='left'>回复：</div><div class='right'><textarea name='replay' id='replay' cols='45' rows='5' class='textarea'>".$list['replay']."</textarea> *</div></li><li><div class='left'></div><div class='right'><input type='hidden' name='id' id='id' value='".$list['id']."'><input type='submit' name='button' id='button' value='提交' class='btn' /></div></li></ul></div></form></div></li></ul><div class='clear'></div></div></div>";
	echo $var_temp;
	//echo $list['name'];
}
//添加回复
function addreplay($id){
	global $db;
	$replay = Text2Html(getvar('replay'));
	//die($replay);
	$record = array(
		'replay'		=>$replay,
		'state'			=> 1,
		'replaytime'	=>date ( "Y-m-d H:i:s" )
	);
	
	$db->update('luo_booklist',$record,'id='.$id);
	header("Location: index.php?act=login");
}
//分页
function pagelist($sql,$pagesize,$url,$page){
	global $db;
	$p = (getvar('page')=='')?1:getvar('page');
	$totle_nums=$db->getRowsNum($sql);
	$page_nums=ceil($totle_nums/$pagesize);
	$pre_page=($page-1)<1?1:$page-1;
	$next_page=($page+1)>$page_nums?$page_nums:$page+1;
	
	$var_temp= '<div class=page><span><strong>'.$pagesize.'/'.$totle_nums.'</strong></span><a href='.$url.'page=1><<</a><a href='.$url.'page='.$pre_page.'><</a>';
	for($i=1;$i<$page_nums+1;$i++){
		if($p==$i){
			$var_temp.= '<a href='.$url.'page='.$i.' class=in>'.$i.'</a>';
		}
		else{
			$var_temp.= '<a href='.$url.'page='.$i.'>'.$i.'</a>';
		}
	}
	$var_temp.= '<a href='.$url.'page='.$next_page.'>></a><a href='.$url.'page='.$page_nums.'>>></a></div>';
	return $var_temp;
	
}
?>
