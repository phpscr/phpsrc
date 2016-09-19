
var Editor       = null;
var obj          = null;
var container    = null;
var editobj      = null;
var tid          = '';
var origsubject  = '';
var editor_state = false;

function amodify(aid){
	try{
		AJAX_value();
	}
	catch(e){}
	tid=aid;
	obj=get_object('attach_'+tid);
	AJAX_Editor(obj);
}
function AJAX_Editor(obj){
	container    = obj.parentNode;
	editobj      = null;
	editor_state = false;
	AJAX_edit();
}
function AJAX_edit(){
	if (editor_state == false){
		Ajaxobj = AJAX_creat();
		editobj = container.insertBefore(Ajaxobj,obj);
		editobj.select();
		obj.style.display = 'none';
		editor_state = true;
	}
}
function AJAX_creat(){
	Ajaxobj            = document.createElement('input');
	Ajaxobj.type       = 'file';
	Ajaxobj.className  = 'input file';
	Ajaxobj.size       = 20;
	Ajaxobj.maxLength  = 100;
	Ajaxobj.name       = 'replace'+tid;
	//Ajaxobj.onblur   = AJAX_value;
	return Ajaxobj;
}
function AJAX_value(){
	if(editor_state == false)return;
	if(editobj.value==''){
		container.removeChild(editobj);
		obj.style.display = '';	
	}
	editor_state = false;
	obj          = null;
	tid          = null;
}