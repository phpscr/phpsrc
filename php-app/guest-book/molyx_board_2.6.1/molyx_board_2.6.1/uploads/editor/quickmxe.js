var fEdit = false;
var canwMode = false;
var wMode = false;
var rng;
var eDir, iDir;
var mxe, mxeWin, mxeDoc, mxeTxa, mxeTxH, mxeEbox, mxeStatus;
var mxeWidth, mxeHeight;
var popOpen;
var mBut = ["removeformat","|","fontname","fontsize","|","bold","italic","underline","|","forecolor","hilitecolor","|","insertlink","unlink","insertimage","quote"];
var mButL =[lang_e['bu_rmformat'],"|",lang_e['bu_ftname'],lang_e['bu_ftsize'],"|",lang_e['bu_bold'],lang_e['bu_italic'],lang_e['bu_unline'],"|",lang_e['bu_foolor'],lang_e['bu_hicolor'],"|",lang_e['bu_inlink'],lang_e['bu_unlink'],lang_e['bu_inimage'],lang_e['bu_quote']];
var popM = ["fontname","fontsize","forecolor","hilitecolor"];
var fonts = [lang_e['bu_st'],lang_e['bu_ht'],lang_e['bu_gt'],lang_e['bu_yy'],lang_e['bu_fs'],lang_e['bu_xm'],"Arial","Courier New","Times New Roman","Verdana"];
var arrColors=[["#800000","#8b4513","#006400","#2f4f4f","#000080","#4b0082","#800080","#000000"],["#ff0000","#daa520","#6b8e23","#708090","#0000cd","#483d8b","#c71585","#696969"],["#ff4500","#ffa500","#808000","#4682b4","#1e90ff","#9400d3","#ff1493","#a9a9a9"],["#ff6347","#ffd700","#32cd32","#87ceeb","#00bfff","#9370db","#ff69b4","#dcdcdc"],["X","#ffffe0","#98fb98","#e0ffff","#87cefa","#e6e6fa","#dda0dd","#ffffff"]];
var quickmxemenu;

function initmxe(eD, wMod) {
	if (document.getElementById) fEdit = true;
	if (fEdit && document.designMode && !isSafari && !isKonqueror) canwMode = true;
	eDir = eD;
	iDir = eDir + "images/";
	if (canwMode) {
		var cookiemode = get_cookie('mxeditor');
		if (cookiemode == 'wysiwyg')  wMode = 1;
		else if (cookiemode == 'bbcode')  wMode = 0;
		else {
			wMode = wMod;
			var modeType = wMode ? 'wysiwyg' : 'bbcode';
			set_cookie('mxeditor', modeType);
		}
	} else wMode = 0;
	if (fEdit) {
		document.writeln('<style type="text/css">@import "' + eDir + 'mxe.css";</style>');
		document.writeln('<link rel="stylesheet" type="text/css" href="' + eDir + 'mxe.css" />');
	}
}

function mxeditor(mxeH, qMode) {
	mxe = mxeH + "W";
	mxeTxH = document.getElementById(mxeH);
	if (qMode != null) {
		quickmxemenu = qMode;
	} else {
		quickmxemenu = "1";
	}
	if (fEdit) {
		mxeWidth = (mxeTxH.offsetWidth > 500 ? mxeTxH.offsetWidth : 500) -12;
		mxeHeight = (mxeTxH.offsetHeight > 100 ? mxeTxH.offsetHeight : 100) -12;
		mxeTxH.style.position = "absolute";
		showHideElement(mxeTxH, 'hide');
		var editDiv = document.createElement('div');
		editDiv.id = 'eDiv_' + mxe;
		editDiv.className = 'div_editor';
		editDiv.style.width = mxeWidth + 'px';
		editDiv.style.height = mxeHeight + 'px';
		if (quickmxemenu == "1") {
			mButDiv = initMenu();
			editDiv.appendChild(mButDiv);
		}
		mxeEbox = initEbox();
		editDiv.appendChild(mxeEbox);
		mxeStatus = initEstatus();
		editDiv.appendChild(mxeStatus);
		mxeTxH.parentNode.appendChild(editDiv);
		if (quickmxemenu == "1") {
			var popusDiv = document.createElement('div');
			popusDiv.id = mxe + 'popus';
			for (var n in popM) {
				var popmenu;
				switch (popM[n]) {
					case 'fontname': popmenu = initPopu('fontname', '150px', '180px', 'auto'); popuFontname(popmenu); break;
					case 'fontsize': popmenu = initPopu('fontsize', 'auto', 'auto', 'visible'); popuFontsize(popmenu); break;
					case 'forecolor': popmenu = initPopu('forecolor', 'auto', 'auto', 'visible'); popuFontcolor(popmenu, "forecolor"); break;
					case 'hilitecolor': popmenu = initPopu('hilitecolor', 'auto', 'auto', 'visible'); popuFontcolor(popmenu, "hilitecolor"); break;
				}
				popusDiv.appendChild(popmenu);
			}
			editDiv.appendChild(popusDiv);
		}
		initData();
	} else return;
}

function cd_switchtext(data) {
	var modeType = wMode ? 'wysiwyg' : 'bbcode';
	set_cookie('mxeditor', modeType);
	var editDiv = document.getElementById('eDiv_' + mxe);
	editDiv.removeChild(mxeEbox);
	editDiv.removeChild(mxeStatus);
	mxeEbox = initEbox();
	mxeStatus = initEstatus();
	editDiv.appendChild(mxeEbox);
	editDiv.appendChild(mxeStatus);
	mxeTxH.value = data;
	initData();
}

function changeMxeMode(fid) {
	if (SystemAjax == '0') {
		alert(lang_e['info_ch']);
	} else {
		var tempMode = wMode;
		if (wMode) {
			mxeTxH.value = get_xhtml(mxeWin.document.body);
			wMode = 0;
		} else {
			mxeTxH.value = mxeTxa.value;
			wMode = 1;
		}
		var obj = my_getbyid('allowsmile');
		if (obj) {
			if (obj.checked) {
				var allowsmile = 1;
			} else {
				var allowsmile = 0;
			}
		} else {
			var allowsmile = 1;
		}
		x_switchtext(mxeTxH.value.replace(/%/g, '%25'), tempMode, fid, allowsmile, cd_switchtext);
	}
}

function initEbox() {
	var boxH = mxeHeight - 23;
	if (quickmxemenu == "1") {
		boxH = boxH - 22;
	}
	if (wMode) {
		var mxeEdBox = document.createElement('iframe');
		mxeEdBox.id = mxe;
		mxeEdBox.name = mxe;
		mxeEdBox.className = 'box_editor';
		mxeEdBox.scrolling = 'yes';
		if(isIE) {
			mxeEdBox.style.height = boxH -2 + 'px';
			mxeEdBox.style.marginTop = '2px';
		} else mxeEdBox.style.height = boxH + 'px';
	}
	else {
		var mxeEdBox = document.createElement('textarea');
		mxeEdBox.id = mxe;
		mxeEdBox.className = 'box_editor';
		mxeEdBox.style.height = boxH + 'px';
		if (isIE) mxeEdBox.attachEvent('onmouseup', hidePopu);
		else mxeEdBox.addEventListener('mouseup', hidePopu, true);
	}
	return mxeEdBox;
}

function initEstatus() {
	var modechange = '';
	if (canwMode) {
		if (wMode) var tomode = lang_e['md_bbcode'];
		else var tomode = lang_e['md_wysiwyg'];
		modechange = ' | <a href="javascript:changeMxeMode(thisforum);">' + lang_e['info_qh'] + tomode + lang_e['info_edi'] + '</a>';
	}
	var editstatus = document.createElement('div');
	editstatus.id = 'editstatus' + mxe;
	editstatus.innerHTML = '<div style="float:right;"><a href="javascript:resizeMxe(1);">&darr;'+lang_e['info_kz']+'</a> | <a href="javascript:resizeMxe(-1);">&uarr;'+lang_e['info_ss']+'</a></div><div><a href="javascript:checklength();">'+lang_e['info_chsize']+'</a>' + modechange + '</div>';
	return editstatus;
}

function initMenu() {
	var mButDiv = document.createElement('div');
	mButDiv.id = 'menubutton' + mxe;
	mButDiv.onmouseover = buttonover;
	mButDiv.onmouseout  = buttonnormal;
	mButDiv.onmousedown = buttondown;
	mButDiv.onmouseup   = buttonover;
	var mButTable = document.createElement('table');
	mButTable.id =  mxe + "_buttons";
	mButTable.className = "e_menu";
	mButTable.cellPadding = "0";
	mButTable.cellSpacing = "1";

	var mButTR = mButTable.insertRow(-1);
	for (var n in mBut)
	{
		var mButTD = mButTR.insertCell(-1);
		if(mBut[n] == '|') {
			mButTD.innerHTML = '<img src="' + iDir + 'sep.gif" border="0" alt="">';
		} else {
			mButTD.id = mBut[n] + '_mxButton_' +mxe ;
			mButTD.className = 'b_normal';
			mButTD.title = mButL[n];
			mButTD.onclick = mxeCmd;
			var mButPic = document.createElement('img');
			mButPic.src = iDir + mBut[n] + '.gif';
			mButPic.alt = mButL[n];
			mButTD.appendChild(mButPic);
		}
	}
	var mButTDE = mButTR.insertCell(-1);
	mButTDE.width = "100%";
	mButDiv.appendChild(mButTable);
	return mButDiv;
}

function initPopu (cmd, width, height, overflow)
{
	var menu = document.createElement('div');

	menu.id = mxe + 'pop' + cmd;
	menu.style.visibility = 'hidden';
	menu.style.display = 'none';
	menu.className = 'pop_editor';
	menu.style.width = width;
	menu.style.height = height;
	menu.onmouseover = buttonover;
	menu.onmouseout  = buttonnormal;
	menu.onmousedown = buttondown;
	menu.onmouseup   = buttonover;

	return menu;
}

function popuFontname (menu)
{
	for (var n in fonts)
	{
		var option = document.createElement('div');
		option.id = 'fontname_sp_' + fonts[n] +'_sp_'+ mxe;
		option.innerHTML = '<font face="' + fonts[n] + '">' + fonts[n] + '</font>';
		option.style.textAlign = 'left';
		option.className = 'b_normal';
		option.title = fonts[n];
		option.onclick = changeFont;
		menu.appendChild(option);
	}
}

function popuFontsize (menu)
{
	for (i = 1; i < 8; i++)
	{
		var option = document.createElement('div');
		option.id = 'fontsize_sp_' + i +'_sp_'+ mxe;
		option.innerHTML = '<font size="' + i + '">' + i + '</font>';
		option.style.textAlign = 'center';
		option.className = 'b_normal';
		option.title = i;
		option.onclick = changeFont;
		menu.appendChild(option);
	}
}

function popuFontcolor (menu, color)
{
	var colorTable = document.createElement('table');
	colorTable.cellPadding = "1";
	colorTable.cellSpacing = "1";
	colorTable.style.fontSize = "1px";

	for (var n in arrColors)
	{
		var colorTR = colorTable.insertRow(-1);
		for (var m in arrColors[n])
		{
			var colorTD = colorTR.insertCell(-1);
			colorTD.id = color + '_sp_' + arrColors[n][m] + '_sp_' + mxe;
			colorTD.className = 'b_normal';
			colorTD.onclick = changeFont;
			var colorDiv = document.createElement('div');
			colorDiv.style.width = '11px';
			colorDiv.style.height = '11px';
			colorDiv.style.border = 'white 1px solid';
			colorDiv.style.fontSize = '1px';
			if (arrColors[n][m] == 'X') {
				colorDiv.innerHTML = 'X';
				colorDiv.style.font = '11px Arial';
				colorDiv.style.textAlign = 'center';
				colorDiv.style.lineHeight = '11px';
			}
			else colorDiv.style.background = arrColors[n][m];
			colorTD.appendChild(colorDiv);
		}
	}
	menu.appendChild(colorTable);
}

function initData() {
	var	html = mxeTxH.value;
	html = initialize(html);
	if (wMode) {
		if (isIE)  mxeWin = frames[mxe];
		else mxeWin = document.getElementById(mxe).contentWindow;
		mxeDoc = mxeWin.document;
		mxeTxa = null;
		enableDesignMode(html);
		if (quickmxemode == "2") {
			mxeWin.focus();
		}
	} else {
		mxeWin = window;
		mxeDoc = mxeWin.document;
		mxeTxa = document.getElementById(mxe);
		mxeTxa.value = html;
		if (isIE) {
			try {
				mxeTxa.attachEvent("onkeypress", function evt_ie_keypress(event) {noKeyPress(event);});
			} catch (e) {
				return false;
			}
		} else {
			try {
				if (isGecko) mxeTxa.addEventListener("keypress", noKeyPress, true);
			} catch (e) {
				return false;
			}
		}
		if (quickmxemode == "2") {
			mxeTxa.focus();
		}
	}
}

function enableDesignMode(html) {
	var frameHtml = "<html id=\"" + mxe + "\">\n";
	frameHtml += "<head>\n";
	frameHtml += "<style type=\"text/css\">@import \"" + eDir + "mxebox.css\";</style>\n";
	frameHtml += "<link rel=\"stylesheet\" type=\"text/css\" href=\"" + eDir + "mxebox.css\" />\n";
	frameHtml += "</head>\n";
	frameHtml += "<body id=\"qmxe\">\n";
	frameHtml += html + "\n";
	frameHtml += "</body>\n";
	frameHtml += "</html>";
	if (isIE) {
		try {
			mxeDoc.designMode = "on";
			mxeDoc.open();
			mxeDoc.write(frameHtml);
			mxeDoc.close();
			mxeWin.document.attachEvent("onkeypress", function evt_ie_keypress(event) {ieKeyPress(event, mxe);});
			mxeWin.document.attachEvent('onmouseup', hidePopu);
		} catch (e) {
			return false;
		}
	} else {
		try {
			mxeDoc.designMode = "on";
			mxeDoc.open();
			mxeDoc.write(frameHtml);
			mxeDoc.close();
			if (isGecko) mxeDoc.addEventListener("keypress", ffKeyPress, true);
			mxeDoc.addEventListener('mouseup', hidePopu, true);
		} catch (e) {
			if (isGecko) setTimeout("enableDesignMode('" + html + "');", 10);
			else return false;
		}
	}
}

function mxeGet() {
	if (fEdit && mxeTxH) {
		if (mxeTxH.value == null) mxeTxH.value = "";
		if (wMode) mxeTxH.value = get_xhtml(mxeWin.document.body);
		else mxeTxH.value = mxeTxa.value;
		if (stripHTML(mxeTxH.value.replace("&nbsp;", " ")) == "" && mxeTxH.value.toLowerCase().search("<hr") == -1 && mxeTxH.value.toLowerCase().search("<img") == -1) mxeTxH.value = "";
	}
	return;
}

function getE(e) {
	var el;
	if (isIE) el = window.event.srcElement;
	else el = e.target;
	return el;
}

function buttonstatus(e,sta) {
	var el = getE(e);
	var className = el.className;
	if (!className && el.parentNode.className) {
		el = el.parentNode;
		className = el.className;
	}
	if (className.substr(0, 2) == 'b_') {
		el.className = 'b_'+sta;
	}
}

function buttonover(e) {
	buttonstatus(e,'over');
}

function buttonnormal(e) {
	buttonstatus(e,'normal');
}

function buttondown(e) {
	buttonstatus(e,'down');
}

function mxeCmd(e) {
	if (wMode && !mxeWin.focus()) mxeWin.focus();
	else if (!wMode && !mxeTxa.focus()) mxeTxa.focus();
	var el = getE(e);
	if (!el.id && el.parentNode.id) {
		el = el.parentNode;
	}
	var cmd = el.id.replace('_mxButton_' + mxe, '');
	hidePopu();
	if(wMode) {
		switch(cmd) {
			case 'bold':
			case 'italic':
			case 'underline':
			case 'unlink':
			case 'removeformat':
				mexcCommand(cmd);
				break;

			case 'fontname':
			case 'fontsize':
			case 'forecolor':
			case 'hilitecolor':
				showPopuDiv(cmd);
				break;

			case 'insertlink':
				showPopuWin(cmd);
				break;

			case 'insertimage':
				var imgpath = prompt(lang_e['info_inimg'],'http://');
				if (imgpath && imgpath != 'http://') {
					mexcCommand('insertImage', false, imgpath);
				}
				break;

			case 'quote':
				wrapTag(cmd, false);
				break;

			default:
				break;
		}
	} else {
		mxeBBcode(cmd);
	}
}

function mxeBBcode(cmd) {
	var tagname;
	switch (cmd) {
		case 'bold':
		case 'italic':
		case 'underline':
			wrapTag(cmd.substr(0, 1), false);
			break;
		case 'quote':
			wrapTag(cmd, false);
			break;

		case 'fontname':
		case 'fontsize':
		case 'forecolor':
		case 'hilitecolor':
			showPopuDiv(cmd);
			break;

		case 'insertlink':
			var linkurl = prompt(lang_e['info_inurl'],'http://');
			if (linkurl && linkurl != 'http://') {
				wrapTag('url', linkurl);
			}
			break;
		case 'unlink':
			var sel = getSelection();
			sel = stripBBcode('url', sel);
			sel = stripBBcode('url', sel, true);
			insertText(sel);
			break;
		case 'insertimage':
			var imgpath = prompt(lang_e['info_inimg'],'http://');
			if (imgpath && imgpath != 'http://') {
				wrapTag('img', false, imgpath);
			}
			break;
		case 'removeformat':
			var needstrip = [['b', 'i', 'u'],['font', 'size', 'color', 'bgcolor']];
			var sel = getSelection();
			if (sel) {
				for(var n in needstrip[0]) {
					sel = stripBBcode(needstrip[0][n], sel);
				}
				for(var n in needstrip[1]) {
					sel = stripBBcode(needstrip[1][n], sel, true);
				}
				insertText(sel);
			}
			break;

		default:
			break;
	}
}

function mexcCommand(cmd, dialog, option)
{
	try {
		mxeWin.focus();
		mxeWin.document.execCommand(cmd, dialog, option);
		mxeWin.focus();
	} catch (e) {
	}
}

function hidePopu()
{
	if (!popOpen) return;
	else {
		showHideElement(popOpen, 'hide');
		popOpen = '';
	}
}

function showPopuDiv(cmd)
{
	var popuDiv = document.getElementById(mxe + 'pop' + cmd);
	if (popOpen && popOpen == popuDiv)
	{
		showHideElement(popuDiv, 'hide');
		popOpen = '';
		return;
	}
	if (popOpen) showHideElement(popOpen, 'hide');
	var popBut = document.getElementById(cmd + '_mxButton_' +mxe);
	popuDiv.style.top = getOffsetTop(popBut) + "px";
	popuDiv.style.left = getOffsetLeft(popBut) + "px";
	showHideElement(popuDiv, 'show');
	setRange(mxe);
	popOpen = popuDiv;
}

function showPopuWin(cmd)
{
	setRange(mxe);
	modalDialogShow(cmd,eDir + cmd + '.php','380','150');
}

function modalDialogShow(cmd,url,width,height)
{
	if (isIE)
	{
		var getValue = window.showModalDialog(url,window, "dialogWidth:"+width+"px;dialogHeight:"+height+"px;edge:Raised;center:1;help:0;resizable:1;maximize:1");
		if (getValue)
		{
			rng.select();
			rng.pasteHTML(getValue);
		}
	} else {
		var ffleft = screen.availWidth/2 - width/2;
		var fftop = screen.availHeight/2 - height/2;
		height -= 50;
		window.open(url, "", "width="+width+"px,height="+height+",left="+ffleft+",top="+fftop);
	}
}

function insert_smilies(id,theSmilie,theFile) {
	if (!mxeTxa){
		if (mxeWin) {
			mxeWin.focus();
		} else {
			return;
		}
	}else{
		mxeTxa.focus();
	}
	setRange(mxe);
	if(wMode) {
		imgpath = "<img src='images/smiles/" + theFile + "' smilietext='" + theSmilie + "' border='0' style='vertical-align:middle' alt='" + theSmilie + "' /> ";
		if (isIE) {
			rng.select();
			rng.pasteHTML(imgpath);
		} else mexcCommand('insertHTML', false, imgpath);
	} else {
		insertText(" " + theSmilie + " ");
	}
}

function changeFont(e) {
	var el = getE(e);
	if (!el.id && el.parentNode.id) {
		el = el.parentNode;
	}
	var cmd = el.id.replace('_sp_' + mxe, '').split('_sp_');
	if (wMode) {
		if (isIE) {
			rng.select();
			if(cmd[0] == 'hilitecolor') cmd[0] = 'backcolor';
		}
		if ((cmd[0] == 'forecolor' || cmd[0] == 'hilitecolor') && cmd[1] == 'X') cmd[1] = '';
		mexcCommand(cmd[0], false, cmd[1]);
	} else {
		if (isIE) rng.select();
		switch (cmd[0]) {
			case 'fontname': wrapTag('font', cmd[1]); break;
			case 'fontsize': wrapTag('size', cmd[1]); break;
			case 'forecolor': {
				if (cmd[1] == 'X'){
					var sel = getSelection();
					sel = stripBBcode('color', sel, true);
					insertText(sel);
					break;
				} else wrapTag('color', cmd[1]); break;
			}
			case 'hilitecolor': {
				if (cmd[1] == 'X'){
					var sel = getSelection();
					sel = stripBBcode('bgcolor', sel, true);
					insertText(sel);
					break;
				} else wrapTag('bgcolor', cmd[1]); break;
			}
		}
	}
	hidePopu();
}

function resizeMxe(change) {
	var newheight = mxeEbox.offsetHeight + change*100;
	if (newheight >= 100) {
		mxeEbox.style.height = newheight + 'px';
		mxeHeight = mxeHeight + change*100;
		document.getElementById('eDiv_' + mxe).style.height = mxeHeight + 'px';
	}
}

function getSelection() {
	var selection;
	setRange(mxe);
	if (wMode) {
		if (isIE) selection = rng.htmlText;
		else selection = rng.toString();
	} else {
		if (isIE) selection = rng.text;
		else {
			if (mxeTxa.selectionEnd <= 2) mxeTxa.selectionEnd = mxeTxa.textLength;
			selection = (mxeTxa.value).substring(mxeTxa.selectionStart, mxeTxa.selectionEnd);
		}
	}
	if (selection === false) selection = '';
	else selection = new String(selection);
	return selection;
}

function insertText(text) {
	if (wMode){
		if (isIE) {
			rng.select();
			rng.pasteHTML(text);
		} else mexcCommand('insertHTML', false, text);
	} else {
		if (isIE) {
			rng.text = text.replace(/\r?\n/g, '\r\n');
			rng.select();
		} else {
			var start  = (mxeTxa.value).substring(0, mxeTxa.selectionStart);
			var end    = (mxeTxa.value).substring(mxeTxa.selectionEnd, mxeTxa.textLength);
			mxeTxa.value = start + text + end;
			var newsel = mxeTxa.selectionStart + (text.length);
			mxeTxa.selectionStart = newsel;
			mxeTxa.selectionEnd   = newsel;
		}
	}
}

function stripBBcode(tag, str, option) {
	if (option == true) var opentag = '[' + tag + '=';
	else var opentag = '[' + tag + ']';
	var closetag = '[/' + tag + ']';
	while ((startindex = stripos(str, opentag)) !== false) {
		if ((stopindex = stripos(str, closetag)) !== false)	{
			if (option == true) {
				var openend = stripos(str, ']', startindex);
				if (openend !== false && openend > startindex && openend < stopindex) var text = str.substr(openend + 1, stopindex - openend - 1);
				else break;
			} else var text = str.substr(startindex + opentag.length, stopindex - startindex - opentag.length);
			str = str.substr(0, startindex) + text + str.substr(stopindex + closetag.length);
		} else break;
	}
	return str;
}

function stripos(str, needle) {
	var index = str.toLowerCase().indexOf(needle.toLowerCase(), 0);
	return (index == -1 ? false : index);
}

function wrapTag(tagname, useoption, content) {
	tagname = tagname.toUpperCase();
	var selection = getSelection();
	if(typeof content != 'undefined' && content != true) selection = content;
	if (useoption === true)
	{
		var option = prompt(lang_e['info_ftinfo']+'[' + tagname + ']'+lang_e['info_hdinfo']+':','');
		if (option) var opentag = '[' + tagname + '="' + option + '"' + ']';
		else return false;
	}
	else if (useoption !== false) var opentag = '[' + tagname + '=' + useoption + '' + ']';
	else var opentag = '[' + tagname + ']';

	var closetag = '[/' + tagname + ']';
	if (tagname == 'URL' && selection == '') selection = useoption;
	var text = opentag + selection + closetag;
	insertText(text);
}

function getOffsetTop(elm) {
	var mOffsetTop = elm.offsetHeight;
	mOffsetTop += elm.offsetParent.offsetTop;
	mOffsetTop += mxeTxH.offsetTop;
	return mOffsetTop;
}

function getOffsetLeft(elm) {
	var mOffsetLeft = elm.offsetLeft -1;
	mOffsetLeft += elm.offsetParent.offsetLeft;
	mOffsetLeft += mxeTxH.offsetLeft;
	return mOffsetLeft;
}

function showHideElement(element, showHide) {
	if (document.getElementById(element)) {
		element = document.getElementById(element);
	}

	if (showHide == 'show') {
		element.style.visibility = 'visible';
		element.style.display = 'block';
	} else if (showHide == 'hide') {
		element.style.visibility = 'hidden';
	}
}

function setRange(mxe) {
	if (isIE) {
		var selection = mxeWin.document.selection;
		if (selection != null) rng = selection.createRange();
	} else {
		if (wMode) {
			var selection = mxeWin.getSelection();
			rng = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
		}
	}
}

function stripHTML(oldString) {
	var newString = oldString.replace(/(<([^>]+)>)/ig,"");
	newString = newString.replace(/\r\n/g," ");
	newString = newString.replace(/\n/g," ");
	newString = newString.replace(/\r/g," ");
	newString = trim(newString);
	return newString;
}

function trim(inputString) {
   if (typeof inputString != "string") return inputString;
   var retValue = inputString;
   var ch = retValue.substring(0, 1);

   while (ch == " ") {
      retValue = retValue.substring(1, retValue.length);
      ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length - 1, retValue.length);

   while (ch == " ") {
      retValue = retValue.substring(0, retValue.length - 1);
      ch = retValue.substring(retValue.length - 1, retValue.length);
   }
   while (retValue.indexOf("  ") != -1) {
      retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ") + 1, retValue.length);
   }
   return retValue;
}

function ffKeyPress(evt) {
	var mxe = evt.target.id;
	if (evt.ctrlKey) {
		if (evt.keyCode == 13) {
			if (mxeTxH.form) {
				mxeGet();
				try
				{
					document.getElementById('querySubmit').click();
				}
				catch (e)
				{
					mxeTxH.form.submit();
				}
			}
		} else {
			var key = String.fromCharCode(evt.charCode).toLowerCase();
			var cmd = '';
			switch (key) {
				case 'b': cmd = "bold"; break;
				case 'i': cmd = "italic"; break;
				case 'u': cmd = "underline"; break;
			}

			if (cmd) {
				mexcCommand(cmd);
				evt.preventDefault();
				evt.stopPropagation();
			}
		}
 	}
}

function ieKeyPress(evt, mxe) {
	if (evt.ctrlKey && evt.keyCode == 10) {
		if (mxeTxH.form) {
			mxeGet();
			try
			{
				document.getElementById('querySubmit').click();
			}
			catch (e)
			{
				mxeTxH.form.submit();
			}
		}
	}
}

function initialize(html) {
	return html;
}

function noKeyPress(evt) {
	if (evt.ctrlKey && ((isIE && evt.keyCode == 10) || (isGecko && evt.keyCode == 13))) {
		document.getElementById('querySubmit').click();
	}
}