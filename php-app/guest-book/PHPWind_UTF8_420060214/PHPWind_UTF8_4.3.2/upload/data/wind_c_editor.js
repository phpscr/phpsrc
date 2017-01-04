function replac()
{
	Rtext=prompt('请输入搜寻目标关键字',"");
	if (Rtext != null)
	{
		if (Rtext != "") 
		{
			Itext=prompt('关键字替换为:',Rtext);
		}
		else
		{
			replac();
		}
		Rtext = new RegExp(Rtext,"g");
		if(C_Mode==true){
			ed.document.body.innerHTML=ed.document.body.innerHTML.replace(Rtext,Itext);
		} else{
			document.FORM.atc_content.value =document.FORM.atc_content.value.replace(Rtext,Itext);
		}
	}
}

function format(what,opt) {
	if (!Error()) return;
	if (opt=="removeFormat"){
		what=opt;opt=null
	}
	if (opt==null){
		ed.document.execCommand(what)
	} else{   
		ed.document.execCommand(what,"",opt)
	}
	ed.focus()
	sel=null
}
function forecolor(url) {
	if (!Error()) return;
	var sm = showModalDialog(url,'',"dialogWidth:250pt;dialogHeight:195pt;help:0;status:0");
	if (sm != ''){
		format('forecolor',sm);
	} else{
		ed.focus();
	}
}
function insertcode(){
	if (!Error()) return;
	code='<br><div align="center"><textarea name=textfield style="overflow:auto;"></textarea></div>';
	var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(code);
		sel.select();
	ed.focus();
}
function quote(){
	if (!Error()) return;
	code='<br><div class=quote></div><br>';
	var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(code);
		sel.select();
	ed.focus();
}
function br(){
	if (!Error()) return;
	code='<br>';
	var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(code);
		sel.select();
	ed.focus();
}
function insertattach(url){
	if (!Error()) return;
	sm=showModalDialog(url,'',"dialogWidth:300pt;dialogHeight:120pt;help:0;status:0");
	if (sm!="") {
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
	ed.focus();
}
function intable(url){
	if (!Error()) return;
	sm=showModalDialog(url,'',"dialogWidth:270pt;dialogHeight:160pt;help:0;status:0");
	if (sm!="") {
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
	ed.focus();
}
function setMode(NewMode) {
	if (NewMode!=C_Mode) {
		if (NewMode) {
			var sContents=Header + document.FORM.atc_content.value
				ed.document.open()
				ed.document.write(sContents)
				ed.document.close()
			document.all.editcontent.style.display='';
			document.FORM.atc_content.style.display='none';
		} else{
			document.all.editcontent.style.display='none';
			document.FORM.atc_content.style.display='';
			str=ed.document.all.tags("BODY")[0].innerHTML;
			document.FORM.atc_content.value=str
			ed.document.body.innerText=getPureHtml(ed.document.body.innerHTML);
		}
		C_Mode=NewMode
		for (var i=0;i<edit.children.length;i++){
			edit.children[i].disabled=(!C_Mode)
		}
	}
	ed.focus()
}
function getPureHtml(){
  var str = ed.document.body.innerHTML;
  return str.substr(1,500);
}
cnt=0;
function checkpost() {
	document.FORM.Submit.disabled=true;
	cnt++;
	if (cnt==1) return true;
	alert('Submission Processing. Please Wait');
	return false;
}
function _submit(){
	if(document.FORM.atc_title.value==''){
		alert('标题为空');
		document.FORM.atc_title.focus();
		return;
	}
	checkpost();
	if(C_Mode){
		setMode(false);
	} else{
		setMode(true);
		
	}
	document.FORM.submit();
}

function Error() {
	if (C_Mode){
		ed.focus();
		return true;
	}
	alert("选择'设计'状态后，才能使用编辑功能！");
	ed.focus();
	return false;
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
function saletable(url){
	if (!Error()) return;
	sm=showModalDialog(url,'',"dialogWidth:330pt;dialogHeight:260pt;help:0;status:0");
	if (sm!="") {
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
	ed.focus();
}
function softtable(url){
	if (!Error()) return;
	sm=showModalDialog(url,'',"dialogWidth:360pt;dialogHeight:390pt;help:0;status:0");
	if (sm!="") {
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
	ed.focus();
}
function addattach(aid){
	if(C_Mode){
		if (!Error()) return;
		code=' [attachment='+aid+'] ';
		var sel
			sel = ed.document.selection.createRange();
			sel.pasteHTML(code);
			sel.select();
	}else{
		AddTxt=' [attachment='+aid+'] ';
		AddText(AddTxt);
	}
	ed.focus();
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
function rming() {
	if (!Error()) return;
	sm=prompt('URL 地址',"http://");
	if(sm!=null) {
		sm="[rm]"+sm+"[/rm]";
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
}
function wmv() {
	if (!Error()) return;
	sm=prompt('URL 地址',"http://");
	if(sm!=null) {
		sm="[wmv]"+sm+"[/wmv]";
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
}
function setswf() {
	if (!Error()) return;
	sm2=prompt('宽度,高度',"400,300");
	if (sm2!=null) {
		sm3=prompt('URL 地址',"http://");
		if (sm3!=null) {
			if (sm2=="") {
				sm="[flash=400,300]"+sm3+"[/flash]";
			} else {
				sm="[flash="+sm2+"]"+sm3+"[/flash]";
			}
		}
		var sel
		sel = ed.document.selection.createRange();
		sel.pasteHTML(sm);
		sel.select();
	}
}