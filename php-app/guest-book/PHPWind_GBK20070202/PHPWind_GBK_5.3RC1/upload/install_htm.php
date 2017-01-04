<!--<?php
print <<<EOT
-->
<html><head><title>PHPWind Installation</title>
<meta http-equiv=Content-Type content="text/html; charset={$lang[db_charset]}">
<style type="text/css">
A { TEXT-DECORATION: none;}
a:hover{ text-decoration: underline;}
.t {font-family: Verdana, Arial, Sans-serif;font-size  : 12px;padding-left: 10px;font-weight: normal;color : #333366;}
.r {font-family: Arial, Sans-serif;font-size  : 12px;font-weight: normal;line-height: 200%;color : #0000EE;}
.c {font-family: Arial, Sans-serif;font-size  : 12px;font-weight: normal;line-height: 200%;color : #EE0000;}
.h {font-family: Arial, Sans-serif;padding-top: 5px;padding-left: 10px;font-size  : 20px;font-weight: bold;color : #000000;}
.i {font-family: Arial, Sans-serif;padding-top: 5px;padding-left: 10px;font-size  : 14px;font-weight: bold;color : #000000;}
table {vertical-align: top;background-color: #f0f0f0;}
</style>
<body vlink='#000000' link='#000000' bgcolor='#6A71A3' leftmargin=0 topmargin=5 marginwidth="0" marginheight="0">
<table width="80%" cellspacing=0 cellpadding=0 align=center valign='center' border=0>

<tr><td class=h valign=top align=left colspan=2>
<span style="COLOR: #cc0000">&gt;&gt;</span> PHPWind 5.x Installation 
<hr align="center" width="99%" size="1" color=#9999cc></td></tr>
<tr><td class='t' valign='top' colspan='2'>$lang[welcome_msg]
<hr align="center" width="99%" size="1" color=#9999cc></td></tr>
<tr><td class='t' valign='top' colspan='2'>
<b>$lang[notice]</b><br>
<span class='r'>$lang[correct]</span><br>
<span class='c'>$lang[incorrect]</span><hr align="center" width="99%" size="1" color=#9999cc>
</td></tr>
<!--
EOT;
if(!$step){
if($error==1){
print <<<EOT
-->
<tr><td class='t'><font color="#FF0000">$lang[lang_error]</font><br><br></td><tr>
<tr><td class='t' align=center>
<input onclick='history.go(-1)' type='button' value='$lang[error_back]' ><br><br></td><tr>
<!--
EOT;
}elseif($error==2){
print <<<EOT
-->
<tr><td class='t'><br><font color="#FF0000">$lang[safe_mode]</font><br><br></td><tr>
<tr><td class='t' align=center>
<input onclick='history.go(-1)' type='button' value='$lang[error_back]'><br><br></td><tr>
<!--
EOT;
}else{
print <<<EOT
-->
<tr><td class='t' align=center><font color="#0000EE"><b>$lang[use_per]</b></font></td></tr>
<tr><td class='t'><b><font color="#99ccff">&gt;</font><font color="#000000"> $lang[please_read]</font></b></td></tr>
<tr><td class='t'><br>$wind_licence</td></tr>
<tr><td align="center"><br>
<form method="post" action='$basename'>
<input type="hidden" name="step" value="2">
<input type="submit" name="submit" value="$lang[agree]">&nbsp;
<input type="button" name="exit" value="$lang[disagree]" onclick="javascript: window.close();">
</form></td></tr>
<!--
EOT;
}}elseif($step==2){
print <<<EOT
-->
<tr><td class='i' colspan='2' align='left'>
<span style='color:#CC0000'>&gt;</span>
$lang[check_write]</td></tr>
<tr><td colspan=2 align=left class='t'>
<br>$lang[base_dir] $state<br>
<!--  
EOT;
foreach($w_check as $key=>$value){
print <<<EOT
-->
$value<br>
<!--  
EOT;
}print <<<EOT
-->
</tr><tr>
<!--
EOT;
if(!$check){
print <<<EOT
-->
<td class='i' colspan='2' align='center'>
<input onclick='history.go(-1)' type='button' value='$lang[error_back]'></td><tr>
<!--
EOT;
}else{
print <<<EOT
-->
<form method="post" action='$basename'>
<input type="hidden" name="step" value="3">
<tr><td class='i' colspan='2' ><hr align="center" width="99%" size="1" color=#9999cc>
<span style='color:#CC0000'>&gt;</span>$lang[admin_set]<br>&nbsp;</td></tr>
<tr><td align=center><br>
<table width="70%" cellspacing=0 cellpadding=1 align=center>
<tr><td bgcolor='#6A71A3'>
<table width="100%" cellspacing=1 cellpadding=3 align=center>
<tr><td class='t' colspan=2 bgcolor='#6A71A3'><font color="#FFFFFF">$lang[server_set]</font></td></tr>
<tr><td class='t' width='40%'>&nbsp;&nbsp;$lang[localhost]</td>
<td class='t'><input type='text' name='SERVER' value='$dbhost'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[data_username]</td>
<td class='t'><input type='text' name='SQLUSER' value='$dbuser'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[data_pwd]</td>
<td class='t'><input type='text' name='SQLPASSWORD' value='$dbpw'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[data_name]</td>
<td class='t'><input type='text' name='SQLNAME' value='$dbname'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[data_pw]</td>
<td class='t'><input type='text' name='SQLZUI' value='$PW'></td></tr>
</table></td></tr></table><br>
<table width="70%" cellspacing=0 cellpadding=1 align=center>
<tr><td bgcolor='#6A71A3'>
<table width="100%" cellspacing=1 cellpadding=3 align=center>
<tr><td class='t' colspan=2 bgcolor='#6A71A3'><font color="#FFFFFF">$lang[manager]</font></td></tr>
<tr><td class='t' width='40%'>&nbsp;&nbsp;$lang[username]</td>
<td class='t'><input type='text' name='INSTALL_NAME' value='$manager'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[password]</td>
<td class='t'><input type='text' name='password'></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[repeat_pwd]</td>
<td class='t'><input type='text' name='password_check'></td></tr>
<tr><td class='t'>&nbsp;&nbsp; Email:</td>
<td class='t'><input type='text' name='adminemail' value='admin@admin.com'></td></tr>
</table></td></tr></table>
</td></tr>
<tr><td align=center colspan=2><br><br><input type='submit' value='$lang[begin_install]'><br><br><br></td></tr>
</form></td></tr>
<!--
EOT;
}}elseif ($step==3){
print <<<EOT
-->
<tr><td class="i" colspan=2 align=left><span style="color:#CC0000">&gt;</span>$lang[check_set]</td></tr>
<!--
EOT;
if($check){
print <<<EOT
-->
<form method="post" action='$basename'>
<input type=hidden value=4 name=step>
<input type=hidden value='$adminemail' name='adminemail'>
<tr><td align=left class=r align=middle colSpan=2>
&nbsp;&nbsp;$lang[write_success]</td></tr>
<tr><td class='i' colspan='2' align='left'><span style='color:#CC0000'>&gt;</span>
&nbsp;$lang[admin_name]</td></tr>
<tr><td class='t' width='50%'>$lang[show_admin] $showpwd</td></tr>
<!--
EOT;
if($D_exists){
print <<<EOT
-->
<tr><td class='i' colspan='2' align='left'><br><hr align="center" width="99%" size="1" color=#9999cc>
<span style='color:#CC0000'>&gt;</span>
&nbsp;$lang[msg_info]</td></tr>
<tr><td class='t' width='50%'><br>
$lang[have_install]
</td></tr>
<!--
EOT;
}print <<<EOT
-->
<tr><td class='t' align='center' width='50%'><br><br>
<table width="96%" cellspacing=0 cellpadding=1 align=center>
<tr><td bgcolor='#6A71A3'>
<table width="100%" cellspacing=1 cellpadding=3 align=center>
<tr><td class='t' colspan=2 bgcolor='#6A71A3'><font color="#FFFFFF">$lang[code_1]</font></td></tr>
<tr>
<td class='t' colspan=2>$lang[code_2]</td>
</tr>
<tr>
<td class='t' colspan=2>
	<input type="checkbox" name="ifopen_s" value='1'>$lang[code_4]
	<input type="checkbox" name="ifopen_t" value='1' checked>$lang[code_3]	
</td>
</tr>
</table></td></tr></table>
<br>
<!--
EOT;
if($D_exists){
print <<<EOT
-->
<input type='submit' value='$lang[goon]' onclick="return checkset('$lang[install_confirm]')">
<!--
EOT;
}else{
print <<<EOT
-->
<input type='submit' value='$lang[goon]'>
<!--
EOT;
}print <<<EOT
-->
<input onclick='history.go(-1)' type='button' value='$lang[back]'>
<br><br></td></tr>
</form>
<!--
EOT;
}else{
print <<<EOT
-->
<tr><td align=left class=c colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$lang[pwd_error]</td></tr>
<tr><td class='i' colspan='2' align='left'>
</tr><tr><td class='i' colspan='2' align='center'>
<input onclick='history.go(-1)' type='button' value='$lang[error_back]'><br><br></td><tr>
<!--
EOT;
}}if($step==4){print <<<EOT
-->
<tr><td class='t' width='50%'>$lang[creat_success] <font color='red'>$dbname</font> ... <br><br></td></tr>
<tr><td class='t' width='50%'>$installinfo</td></tr>
<tr><td class='i'><br><hr align="center" width="99%" size="1" color=#9999cc>
<span style='color:#CC0000'>&gt;</span>
&nbsp;$lang[msg_info]</td></tr>
<tr><td class='t' width='50%'><br>$lang[www_url]</td></tr>
<tr><td class='t' width='50%'>$lang[bbs_url]</td></tr>
<tr><td align=center><br><br>
<form method="post" action='$basename'>
<input type=hidden value=5 name=step>
<table width="70%" cellspacing=0 cellpadding=1 align=center>
<tr><td bgcolor='#6A71A3'>
<table width="100%" cellspacing=1 cellpadding=3 align=center>
<tr><td class='t' colspan=2 bgcolor='#6A71A3'><font color="#FFFFFF">$lang[urlset]</font></td></tr>
<tr><td class='t' width='40%'>&nbsp;&nbsp;$lang[wwwurl]</td>
<td class='t'><input type='text' name='wwwurl' value='$wwwurl' size=30></td></tr>
<tr><td class='t'>&nbsp;&nbsp;$lang[bbsurl]</td>
<td class='t'><input type='text' name='bbsurl' value='$bbsurl' size=30></td></tr>
</table></td></tr></table><br><br></td></tr>
<tr><td align=center colspan=2><br><br><input type='submit' value='$lang[complete]'><br><br></td></tr>
</form>
<!--
EOT;
}elseif($step==5){print <<<EOT
-->
<tr><td class='i' width='50%'>
<span style='color:#CC0000'>&gt;</span>
<a href='admin.php'>$lang[enter_bbs]</a><br><br></td></tr>
$unlinkerror
<!--
EOT;
}print <<<EOT
-->
</table></body></html>
<!--
EOT;
?>-->
<SCRIPT LANGUAGE='JavaScript'>
function checkset(chars)
{	
	if(!confirm(chars))
		return false;
}
</script>