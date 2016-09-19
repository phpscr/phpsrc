var defaultmode = "divmode";
var text = "";

if (defaultmode == "nomode") {
        helpmode = false;
        divmode = false;
        nomode = true;
} else if (defaultmode == "helpmode") {
        helpmode = true;
        divmode = false;
        nomode = false;
} else {
        helpmode = false;
        divmode = true;
        nomode = false;
}
function checkmode(swtch){
	if (swtch == 1){
		nomode = false;
		divmode = false;
		helpmode = true;
		alert('Wind 代码 - 帮助信息\n\n点击相应的代码按钮即可获得相应的说明和提示');
	} else if (swtch == 0) {
		helpmode = false;
		divmode = false;
		nomode = true;
		alert('Wind 代码 - 直接插入\n\n点击代码按钮后不出现提示即直接插入相应代码');
	} else if (swtch == 2) {
		helpmode = false;
		nomode = false;
		divmode = true;
		alert('Wind 代码 - 提示插入\n\n点击代码按钮后出现向导窗口帮助您完成代码插入');
	}
}
function getActiveText(selectedtext) {
  text = (document.all) ? document.selection.createRange().text : document.getSelection();
  if (selectedtext.createTextRange) {	
    selectedtext.caretPos = document.selection.createRange().duplicate();	
  }
	return true;
}
function submitonce(theform)
{
	if (document.all||document.getElementById)
	{
		for (i=0;i<theform.length;i++)
		{
			var tempobj=theform.elements[i];
			if(tempobj.type.toLowerCase()=="submit"||tempobj.type.toLowerCase()=="reset")
				tempobj.disabled=true;
		}
	}
}
function checklength(theform)
{
	alert('已有字节数'+theform.atc_content.value.length);
}
function AddText(NewCode) 
{
	if (document.FORM.atc_content.createTextRange && document.FORM.atc_content.caretPos) 
	{
		var caretPos = document.FORM.atc_content.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? NewCode + ' ' : NewCode;
	} 
	else 
	{
		document.FORM.atc_content.value+=NewCode
	}
	setfocus();
}
function setfocus()
{
  document.FORM.atc_content.focus();
}


function showsize(size) {
	if (helpmode) {
		alert('文字大小标记\n设置文字大小.\n可变范围 1 - 6.\n 1 为最小 6 为最大.\n用法: '+"[size="+size+"] "+size+'文字'+"[/size]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[size="+size+"]"+text+"[/size]";
		AddText(AddTxt);
	} else {
		txt=prompt('','文字');
		if (txt!=null) {
			AddTxt="[size="+size+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/size]";
			AddText(AddTxt);
		}
	}
}

function showfont(font) {
 	if (helpmode){
		alert('字体标记\n给文字设置字体.\n用法: '+" [font="+font+"]"+font+"[/font]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[font="+font+"]"+text+"[/font]";
		AddText(AddTxt);
	} else {
		txt=prompt('要设置字体的文字'+font,'文字');
		if (txt!=null) {
			AddTxt="[font="+font+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/font]";
			AddText(AddTxt);
		}
	}
}
function showcolor(color) {
	if (helpmode) {
		alert('颜色标记\n设置文本颜色.  任何颜色名都可以被使用.\n用法: '+"[color="+color+"]"+color+"[/color]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[color="+color+"]"+text+"[/color]";
		AddText(AddTxt);
	} else {  
     	txt=prompt('选择的颜色是: '+color,'文字');
		if(txt!=null) {
			AddTxt="[color="+color+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/color]";
			AddText(AddTxt);
		}
	}
}

function bold() {
	if (helpmode) {
		alert('加粗标记\n使文本加粗.\n用法: [b]这是加粗的文字[/b]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[b]"+text+"[/b]";
		AddText(AddTxt);
	} else {
		txt=prompt('文字将被变粗.','文字');
		if (txt!=null) {
			AddTxt="[b]"+txt;
			AddText(AddTxt);
			AddTxt="[/b]";
			AddText(AddTxt);
		}
	}
}

function italicize() {
	if (helpmode) {
		alert('斜体标记\n使文本字体变为斜体.\n用法: [i]这是斜体字[/i]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[i]"+text+"[/i]";
		AddText(AddTxt);
	} else {
		txt=prompt('文字将变斜体','文字');
		if (txt!=null) {
			AddTxt="[i]"+txt;
			AddText(AddTxt);
			AddTxt="[/i]";
			AddText(AddTxt);
		}
	}
}

function quoteme() {
	if (helpmode){
		alert('引用标记\n引用一些文字.\n用法: [quote]引用内容[/quote]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[quote]"+text+"[/quote]";
		AddText(AddTxt);
	} else {
		txt=prompt('被引用的文字','文字');
		if(txt!=null) {
			AddTxt="[quote]"+txt;
			AddText(AddTxt);
			AddTxt="[/quote]";
			AddText(AddTxt);
		}
	}
}
function setfly() {
 	if (helpmode){
		alert('飞行标记\n使文字飞行.\n用法: [fly]文字为这样文字[/fly]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[fly]"+text+"[/fly]";
		AddText(AddTxt);
	} else {
		txt=prompt('飞行文字','文字');
		if (txt!=null) {
			AddTxt="[fly]"+txt;
			AddText(AddTxt);
			AddTxt="[/fly]";
			AddText(AddTxt);
		}
	}
}

function movesign() {
	if (helpmode) {
		alert('移动标记\n使文字产生移动效果.\n用法: [move]要产生移动效果的文字[/move]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[move]"+text+"[/move]";
		AddText(AddTxt);
	} else {
		txt=prompt('要产生移动效果的文字','文字');
		if (txt!=null) {
			AddTxt="[move]"+txt;
			AddText(AddTxt);
			AddTxt="[/move]";
			AddText(AddTxt);
		}
	}
}

function shadow() {
	if (helpmode) {
		alert('阴影标记\n使文字产生阴影效果.\n用法: [SHADOW=宽度, 颜色, 边界]要产生阴影效果的文字[/SHADOW]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[SHADOW=255,blue,1]"+text+"[/SHADOW]";
		AddText(AddTxt);
	} else {
		txt2=prompt('文字的长度、颜色和边界大小',"255,blue,1");
		if (txt2!=null) {
			txt=prompt('要产生阴影效果的文字','文字');
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[shadow=255, blue, 1]"+txt;
					AddText(AddTxt);
					AddTxt="[/shadow]";
					AddText(AddTxt);
				} else {
					AddTxt="[shadow="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/shadow]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function glow() {
	if (helpmode) {
		alert('光晕标记\n使文字产生光晕效果.\n用法: [GLOW=宽度, 颜色, 边界]要产生光晕效果的文字[/GLOW]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[glow=255,red,2]"+text+"[/glow]";
		AddText(AddTxt);
	} else {
		txt2=prompt('文字的长度、颜色和边界大小',"255,red,2");
		if (txt2!=null) {
			txt=prompt('要产生光晕效果的文字','文字');
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[glow=255,red,2]"+txt;
					AddText(AddTxt);
					AddTxt="[/glow]";
					AddText(AddTxt);
				} else {
					AddTxt="[glow="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/glow]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function center() {
 	if (helpmode) {
		alert('对齐标记\n使用这个标记, 可以使文本左对齐、居中、右对齐.\n用法: [align=center|left|right]要对齐的文本[/align]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[align=center]"+text+"[/align]";
		AddText(AddTxt);
	} else {
		txt2=prompt('对齐样式\n输入 ’center’ 表示居中, ’left’ 表示左对齐, ’right’ 表示右对齐.',"center");
		while ((txt2!="") && (txt2!="center") && (txt2!="left") && (txt2!="right") && (txt2!=null)) {
			txt2=prompt('错误!\n类型只能输入 ’center’ 、 ’left’ 或者 ’right’.',"");
		}
		txt=prompt('要对齐的文本','文字');
		if (txt!=null) {
			AddTxt="[align="+txt2+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/align]";
			AddText(AddTxt);
		}
	}
}

function rming() {
	if (helpmode) {
		alert('RM音乐标记\n插入一个RM链接标记\n使用方法: [rm]http://www.phpwind.net/rm/php.rm[/rm]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[rm]"+text+"[/rm]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL 地址',"http://");
		if(txt!=null) {
			AddTxt="[rm]"+txt;
			AddText(AddTxt);
			AddTxt="[/rm]";
			AddText(AddTxt);
		}
	}
}

function image() {
	if (helpmode){
		alert('图片标记\n插入图片\n用法: [img]http://www.phpwind.net/image/php.gif[/img]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[img]"+text+"[/img]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL 地址',"http://");
		if(txt!=null) {
			AddTxt="[img]"+txt;
			AddText(AddTxt);
			AddTxt="[/img]";
			AddText(AddTxt);
		}
	}
}

function wmv() {
	if (helpmode){
		alert('wmv标记\n插入wmv\n用法: [wmv]http://www.phpwind.net/wmv/php.wmv[/wmv]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[wmv]"+text+"[/wmv]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL 地址',"http://");
		if(txt!=null) {
			AddTxt="[wmv]"+txt;
			AddText(AddTxt);
			AddTxt="[/wmv]";
			AddText(AddTxt);
		}
	}
}

function showurl() {
 	if (helpmode){
		alert('url标记\n使用url标记,可以使输入的url地址以超链接的形式在帖子中显示.\n使用方法:\n [url]url地址[/url]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[url="+text+"]"+text+"[/url]";
		AddText(AddTxt);
	} else {
			txt2=prompt('URL 名称: 比如 进入 phpwind 论坛(可以为空)',"");
		if (txt2!=null) {
			txt=prompt('URL 地址',"http://");
			if (txt2!=null) {
				if (txt2=="") {
					AddTxt="[url]"+txt;
					AddText(AddTxt);
					AddTxt="[/url]";
					AddText(AddTxt);
				} else {
					if(txt==""){
						AddTxt="[url]"+txt2;
						AddText(AddTxt);
						AddTxt="[/url]";
						AddText(AddTxt);
					} else{
						AddTxt="[url="+txt+"]"+txt2;
						AddText(AddTxt);
						AddTxt="[/url]";
						AddText(AddTxt);
					}
				}
			}
		}
	}
}

function showcode() {
	if (helpmode) {
		alert('代码标记\n使用代码标记,可以使你的程序代码里面的 html 等标志不会被破坏.\n使用方法:\n [code]这里是代码文字[/code]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[code]"+text+"[/code]";
		AddText(AddTxt);
	} else {
		txt=prompt('输入代码',"");
		if (txt!=null) { 
			AddTxt="[code]"+txt;
			AddText(AddTxt);
			AddTxt="[/code]";
			AddText(AddTxt);
		}
	}
}

function list() {
	if (helpmode) {
		alert('列表标记\n建造一个文字或则数字列表.\nUSE: [list]\n[*]item1\n[*]item2\n[*]item3\n[/list]');
	} else if (nomode) {
		AddTxt="[list][*][*][*][/list]";
		AddText(AddTxt);
	} else {
		txt=prompt('列表类型\n输入 ’a’ 表示字母列表, ’1’ 表示数字列表, 留空表示普通列表.',"");
		while ((txt!="") && (txt!="A") && (txt!="a") && (txt!="1") && (txt!=null)) {
			txt=prompt('错误!\n类型只能输入 ’a’、’A’ 、 ’1’ 或者留空.',"");
		}
		if (txt!=null) {
			if (txt==""){
				AddTxt="[list]";
			} else if (txt=="1") {
				AddTxt="[list=1]";
			} else if(txt=="a") {
				AddTxt="[list=a]";
			}
			ltxt="1";
			while ((ltxt!="") && (ltxt!=null)) {
				ltxt=prompt('列表项\n空白表示结束列表',"");
				if (ltxt!="") {
					AddTxt+="[*]"+ltxt+"";
				}
			}
			AddTxt+="[/list]";
			AddText(AddTxt);
		}
	}
}
function underline() {
  	if (helpmode) {
		alert('下划线标记\n给文字加下划线.\n用法: [u]要加下划线的文字[/u]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[u]"+text+"[/u]";
		AddText(AddTxt);
	} else {
		txt=prompt('下划线文字','文字');
		if (txt!=null) {
			AddTxt="[u]"+txt;
			AddText(AddTxt);
			AddTxt="[/u]";
			AddText(AddTxt);
		}
	}
}

function setswf() {
 	if (helpmode){
		alert('Flash 动画\n插入 Flash 动画.\n用法: [flash=宽度,高度]Flash 文件的地址[/flash]');
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[flash=400,300]"+text+"[/flash]";
		AddText(AddTxt);
	} else {
			txt2=prompt('宽度,高度',"400,300");
		if (txt2!=null) {
			txt=prompt('URL 地址',"http://");
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[flash=400,300]"+txt;
					AddText(AddTxt);
					AddTxt="[/flash]";
					AddText(AddTxt);
				} else {
					AddTxt="[flash="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/flash]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function add_title(addTitle)
{ 
	var revisedTitle; 
	var currentTitle = document.FORM.atc_title.value; 
	revisedTitle =addTitle+ currentTitle; 
	document.FORM.atc_title.value=revisedTitle; 
	document.FORM.atc_title.focus(); 
	return;
}
function Addaction(addTitle)
{ 
	var revisedTitle; 
	var currentTitle = document.FORM.atc_content.value; 
	revisedTitle = currentTitle+addTitle; 
	document.FORM.atc_content.value=revisedTitle; 
	document.FORM.atc_content.focus(); 
	return; 
}

function copytext(theField) 
{
	var tempval=eval("document."+theField);
	tempval.focus();
	tempval.select();
	therange=tempval.createTextRange();
	therange.execCommand("Copy");
}

function replac()
{
	if (helpmode)
	{
		alert('替换关键字');
	}
	else
	{
		txt2=prompt('请输入搜寻目标关键字',"");
		if (txt2 != null)
		{
			if (txt2 != "") 
			{
				txt=prompt('关键字替换为:',txt2);
			}
			else
			{
				replac();
			}
			var Rtext = txt2; var Itext = txt;
			Rtext = new RegExp(Rtext,"g");
			document.FORM.atc_content.value =document.FORM.atc_content.value.replace(Rtext,Itext);
		}
	}
}
function addattach(aid){
    AddTxt=' [attachment='+aid+'] ';
	AddText(AddTxt);
}
function addsmile(NewCode) {
    document.FORM.atc_content.value += ' '+NewCode+' '; 
}
cnt = 0;
function checkCnt() {
	document.FORM.Submit.disabled=true;
	cnt++;
	if (cnt!=1){
		alert('Submission Processing. Please Wait');
		return false;
	}
	document.FORM.submit();
}
function _submit(){
	if(document.FORM.atc_title.value==''){
		alert('标题为空');
		document.FORM.atc_title.focus();
		return;
	}
	checkCnt();
}

function quickpost(event)
{
	if((event.ctrlKey && event.keyCode == 13)||(event.altKey && event.keyCode == 83))
	{
		 cnt++;
		 if(cnt==1){
			 this.document.FORM.submit();
		 }else{
			 alert('Submission Processing. Please Wait');
		 }
	}
}
function saletable(url){
	sm=showModalDialog(url,'',"dialogWidth:330pt;dialogHeight:260pt;help:0;status:0");
	if (sm!="") {
		AddText(sm);
	}
	setfocus();
}
function softtable(url){
	sm=showModalDialog(url,'',"dialogWidth:360pt;dialogHeight:390pt;help:0;status:0");
	if (sm!="") {
		AddText(sm);
	}
	setfocus();
}