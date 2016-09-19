<?php
if(isset($_GET['q'])||isset($_GET['sgoon'])||isset($_GET['keyword'])){
	$bname= basename($_SERVER['PHP_SELF']);
	$srhHost="http://so.mdbchina.com/";
	$s=$_GET["sgoon"];
	if (isset($s))
	{
		$keyword=str_replace("+","%20",urlencode($_GET['q']));
		//$keyword=rawurldecode($_GET['q']);
		$url=$srhHost."query/?q=".$keyword."&mode=2&s=".$s;
	}
	else
	{
		$keyword=str_replace("+","%20",urlencode($_GET['keyword']));
		//$keyword=rawurldecode($_GET['keyword']);
		$page=$_GET["page"];
		if (isset($page))
		$url=$srhHost."query/".$keyword."?mode=2&page=".$page;
		else
		$url=$srhHost."query/".$keyword."?mode=2";
	}
	$mdbSrh= file_get_contents($url);
	$mdbSrh=str_replace("action=\"http://so.mdbchina.com/query/\"", "action=\"".$bname."\"", $mdbSrh);

	$mdbSrh=str_replace("/query/".$keyword."?page=", $bname."?keyword=".$keyword."&page=", $mdbSrh);

	$mdbSrh=str_replace("=\"/", "=\"".$srhHost, $mdbSrh);

	$mdbSrh=str_replace("<input type=\"hidden\" name=\"mode\" value=\"2\"/>", "<input type=\"hidden\" name=\"mode\" value=\"2\"/><input type=\"hidden\" name=\"sgoon\" value=\"1\"/>", $mdbSrh);

	echo $mdbSrh;
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>搜索介绍</title>
<style>
BODY {
	FONT-SIZE: 13px; MARGIN: 0px; LINE-HEIGHT: 15px
}
A:link {
	COLOR: #480000; TEXT-DECORATION: none
}
A:visited {
	COLOR: #480000; TEXT-DECORATION: none
}
A:hover {
	COLOR: #333333; TEXT-DECORATION: underline
}
A:active {
	TEXT-DECORATION: none
}
FORM {
	MARGIN-TOP: 3px; MARGIN-BOTTOM: 3px
}
#SearchWelcome {
	PADDING-RIGHT: 5px; PADDING-LEFT: 5px; PADDING-BOTTOM: 5px;  WIDTH: 100%; PADDING-TOP: 5px; 
}
#SearchError {
	 PADDING-RIGHT: 5px; PADDING-LEFT: 5px; PADDING-BOTTOM: 5px; PADDING-TOP: 5px;
}
#SearchWelcome {
	COLOR: white; BACKGROUND-COLOR: #480000; TEXT-ALIGN: center
}
#SearchError {
	TEXT-ALIGN: left
}
.itemnote{
	padding-top:10px;
}
.itembox{
	padding-top:10px;
}
.itemword{
	padding-left:1px;
	padding-right:1px;
}
.delimiterl{
	position:relative;
	top:-2px;
	display:inline;
	padding:1px;
	padding-right:2px;
}
.delimiterr{
	position:relative;
	top:-2px;
	display:inline;
	padding:1px;
}
</style>
</head>
<body>
<center>
  <table cellpadding="0" cellspacing="1" border="0" width="400px" bgcolor="#480000" style="margin-top:10px;">
    <tbody bgcolor="#FFFFFF">
      <tr>
        <td colspan="2"><DIV id=SearchWelcome >欢迎使用中国影视库资源搜索</DIV></td>
      </tr>
      <tr>
        <td valign="top"><DIV id=SearchError style="float:left;">
            <form action="mdbget.php" method="get">
              <label>
              <input name="keyword" type="text" id="keyword" />
              </label>
              <label>
              <input type="submit" name="Submit" value="搜索" />
              </label>
            </form>
            <div class="itembox"> 请输入影视相关信息进行搜索，在返回结果中：
              <div class="itemnote"> ·点击<span class="itemword"><span class="delimiterl">[</span>插入<span class="delimiterr">]</span> </span>或者<span class="itemword"><span class="delimiterl">[</span>影片名称<span class="delimiterr">]</span> </span></span>链接即可插入影视资料 </div>
              <div class="itemnote"> ·点击<span class="itemword"><span class="delimiterl">[</span>预览<span class="delimiterr">]</span> </span></span>链接查看影视资料 </div>
              <div class="itemnote"> ·点击<span class="itemword"><span class="delimiterl">[</span>地区<span class="delimiterr">]</span> </span></span>或者<span class="itemword"><span class="delimiterl">[</span>类型<span class="delimiterr">]</span> </span></span>链接了解更多同类信息 </div>
              <div class="itemnote"> 如果您对搜索功能还有疑问，请访问<a href="http://help.mdbchina.com/help/admin/news_more.asp?lm2=79" target="_blank"><span class="itemword" style="color:#3399FF;">搜索介绍</span></a>，了解详细功能。 </div>
            </div>
          </DIV></td>
      </tr>
    </tbody>
  </table>
</center>
</body>
</html>