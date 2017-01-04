
var Editor       = null;
var obj          = null;
var linkobj      = null;
var container    = null;
var editobj      = null;
var tid          = '';
var origsubject  = '';
var response     ='';
var editor_state = false;

function AJAX_Init(threadtableid){
	var tds = get_tags(get_object(threadtableid), 'td');
	for (var i = 0; i < tds.length; i++){
		if (tds[i].hasChildNodes() && tds[i].id && tds[i].id.substr(0, 3) == 'td_'){
			tds[i].ondblclick = subject_doubleclick;
		}
	}
}
function subject_doubleclick(){
	try{
		AJAX_store();
	}
	catch(e){}
	Editor = new AJAX_Editor(this);
}
function AJAX_Editor(obj){
	obj          = obj;
	tid          = obj.id.substr(obj.id.lastIndexOf('_') + 1);
	linkobj      = get_object('a_ajax_' + tid);
	container    = linkobj.parentNode;
	editobj      = null;
	editor_state = false;
	AJAX_edit();
}
function AJAX_edit(){
	if (editor_state == false){
		Ajaxobj = AJAX_creat();
		editobj = container.insertBefore(Ajaxobj, linkobj);
		editobj.select();
		origsubject = linkobj.innerHTML;
		linkobj.style.display = 'none';
		editor_state = true;
	}
}
function AJAX_creat(){
	Ajaxobj            = document.createElement('input');
	Ajaxobj.type       = 'text';
	Ajaxobj.size       = 50;
	Ajaxobj.maxLength  = 100;
	AJAX_save('','fetchtitle');
	Ajaxobj.onkeypress = AJAX_onkeypress;
	Ajaxobj.onblur     = AJAX_store;
	return Ajaxobj;
}
function AJAX_save(ajaxtext,action){
	document.AjaxForm.tid.value = tid;
	document.AjaxForm.atc_content.value = ajaxtext;
	document.AjaxForm.action.value = action;
	document.AjaxForm.submit();
}
function AJAX_store(){
	if (editor_state == true){
		AJAX_save(editobj.value,'subject');
		container.removeChild(editobj);
		linkobj.style.display = '';
		editor_state = false;
		obj = null;		
	}
}
function AJAX_onkeypress(e){
	e = e ? e : window.event;
	switch (e.keyCode){
		case 13:
		{
			AJAX_store();
			return false;
		}
		case 27:
		{
			Ajaxobj.value = origsubject;
			AJAX_store();
			return true;
		}
	}
}
function AJAX_response(action,text){
	if(action == 'fetchcode'){
		editobj.value = text;
		origsubject   = text;
	}else if(action == 'showcode'){
		linkobj.innerHTML = text;
	}
}