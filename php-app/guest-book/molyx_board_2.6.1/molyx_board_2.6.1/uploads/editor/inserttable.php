<?php
#**************************************************************************#
#   MolyX2
#   ------------------------------------------------------
#   copyright (c) 2004-2006 HOGE Software.
#   official forum : http://molyx.com
#   license : MolyX License, http://molyx.com/license
#   MolyX2 is free software. You can redistribute this file and/or modify
#   it under the terms of MolyX License. If you do not accept the Terms
#   and Conditions stated in MolyX License, please do not redistribute
#   this file.Please visit http://molyx.com/license periodically to review
#   the Terms and Conditions, or contact HOGE Software.
#**************************************************************************#
define('ROOT_PATH'  , './../');
define ('IN_ACP', 0);
require_once(ROOT_PATH.'includes/init.php');
require_once(ROOT_PATH.'includes/functions.php');
header("Content-Type:text/html; charset=UTF-8"); 
$forums->func = new functions();
$_INPUT = $forums->func->init_variable();
$bboptions['language'] = 'en-us';
$forums->func->check_cache('settings');
$bboptions = $forums->cache['settings'];
$forums->func->check_lang();
$forums->lang = $forums->func->load_lang($forums->lang, 'editor');
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">@import "mxedlg.css";</style>
<title><?php echo $forums->lang['insert_table']; ?></title>
<script language="JavaScript" type="text/javascript">
<!--
function applyTable() {
	var agt=navigator.userAgent.toLowerCase();
	isIE=(agt.indexOf("msie")!=-1 && document.all);

	var html = '<table border="' + document.tableForm.border.value + '" cellpadding="' + document.tableForm.padding.value + '" ';
	
	html += 'cellspacing="' + document.tableForm.spacing.value + '" width="' + document.tableForm.width.value + '">\n';
	for (var rows = 0; rows < document.tableForm.rows.value; rows++) {
		html += "<tr>\n";
		for (cols = 0; cols < document.tableForm.columns.value; cols++) {
			html += "<td>&nbsp;</td>\n";
		}
		html+= "</tr>\n";
	}
	html += "</table>\n";
	
	if (isIE){
		window.returnValue = html;
	} else {
		window.opener.mexcCommand('insertHTML', false, html);
	}
	window.close();
}
//-->
</script>
</head>
<body style='overflow:hidden;'>

<table width='100%' height='100%' align='center' cellpadding='0' cellspacing='0'>
<form name='tableForm'>
<tr>
<td valign='top' style='padding:5px;padding-left:10px;padding-right:10px;'>
	<table>
	<tr>
		<td><?php echo $forums->lang['row_number']; ?>:</td>
		<td><input type='text' name='rows' size='3' value='2'></td>
		<td>&nbsp;</td>
		<td><?php echo $forums->lang['margin']; ?>:</td>
		<td><select name='spacing'>
				<option value='0' selected>0px</option>
				<option value='1'>1px</option>
				<option value='2'>2px</option>
				<option value='3'>3px</option>
				<option value='4'>4px</option>
				<option value='5'>5px</option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td><?php echo $forums->lang['width']; ?>:</td>
		<td><input type='text' name='width' size='5' value='300'></td>
	</tr>
	<tr>
		<td><?php echo $forums->lang['col_number']; ?>:</td>
		<td><input type='text' name='columns' size='3' value='2'></td>
		<td>&nbsp;</td>
		<td><?php echo $forums->lang['padding']; ?>:</td>
		<td><select name='padding'>
				<option value='0' selected>0px</option>
				<option value='1'>1px</option>
				<option value='2'>2px</option>
				<option value='3'>3px</option>
				<option value='4'>4px</option>
				<option value='5'>5px</option>
			</select>
		</td>
		<td>&nbsp;</td>
		<td><?php echo $forums->lang['border']; ?>:</td>
		<td>
			<select name='border'>
				<option value='0'><?php echo $forums->lang['none']; ?></option>
				<option value='1' selected>1px</option>
				<option value='2'>2px</option>
				<option value='3'>3px</option>
				<option value='4'>4px</option>
				<option value='5'>5px</option>
			</select>
		</td>
	</tr>
	</table>
</td>
</tr>
<tr>
<td align='right'>
	<input type='button' value='<?php echo $forums->lang['cancel']; ?>' onclick='window.close();'>&nbsp;
	<input type='button' value='<?php echo $forums->lang['insert_table']; ?>' onclick='applyTable();'>
</td>
</tr>
</form>
</table>

</body>
</html>