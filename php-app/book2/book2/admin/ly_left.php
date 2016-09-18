<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>EIMS</title>
<script type="text/javascript" src="images/js/jquery.js"></script>
<script type="text/javascript" src="images/js/chili-1.7.pack.js"></script>
<script type="text/javascript" src="images/js/jquery.easing.js"></script>
<script type="text/javascript" src="images/js/jquery.dimensions.js"></script>
<script type="text/javascript" src="images/js/jquery.accordion.js"></script>
<script language="javascript">
	jQuery().ready(function(){
		jQuery('#navigation').accordion({
			header: '.head',
			navigation1: true, 
			event: 'click',
			fillSpace: true,
			animated: 'bounceslide'
		});
	});
</script>
</head>
<body>
<div  style="height:100%;">
  <ul id="navigation">
        <li><a href="ly_pwd.php" target="rightFrame">密码修改</a></li>
        <li><a href="ly_system.php" target="rightFrame">网站设置</a></li>
		<li><a href="bbs_admin.php" target="rightFrame">查看留言</a></li>
  </ul>
</div>
</body>
</html>