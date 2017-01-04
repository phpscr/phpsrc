
var Editor       = null;
var obj          = null;
var linkobj      = null;
var container    = null;
var editobj      = null;
var tid          = '';
var origsubject  = '';
var responsetext     ='';
var editor_state = false;

function AJAX_Init(threadtableid){
	var tds = get_tags(get_object(threadtableid), 'div');
	for(var i = 0; i < tds.length; i++){
		if(tds[i].hasChildNodes() && tds[i].id && tds[i].id.substr(0, 7) == 'a_ajax_'){
			tds[i].ondblclick = content_doubleclick;
		}
	}
}
function content_doubleclick(){
	if(editor_state == true){
		return;
	}
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
	if(editor_state == false){
		Ajaxobj = AJAX_creat();
		editobj = container.insertBefore(Ajaxobj,linkobj);
		editobj.select();
		linkobj.style.display = 'none';
		editor_state = true;
	}
}
function AJAX_creat(){
	Ajaxobj        = document.createElement('textarea');
	Ajaxobj.rows   = 8;
	Ajaxobj.cols   = 100;
	AJAX_save('','fetchcode');
	Ajaxobj.onblur = AJAX_store;
	return Ajaxobj;
}
function AJAX_save(ajaxtext,action){
	document.AjaxForm.tid.value = tid;
	document.AjaxForm.atc_content.value = ajaxtext;
	document.AjaxForm.action.value = action;
	document.AjaxForm.submit();
}
function AJAX_store(){
	if(editor_state == true){
		AJAX_save(editobj.value,'content');
		container.removeChild(editobj);
		linkobj.style.display = '';
		editor_state = false;
		obj = null;
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