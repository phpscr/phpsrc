var uagent=navigator.userAgent.toLowerCase();
var isIE, isGecko, isOpera, isSafari, isKonqueror, isWin, isMac, uaVers;
	isIE=(uagent.indexOf("msie")!=-1 && document.all);
	isGecko = (uagent.indexOf("gecko") != -1);
	isOpera = (uagent.indexOf("opera") !=-1);
	isSafari = (uagent.indexOf("safari") != -1);
	isKonqueror = (uagent.indexOf("konqueror") != -1);
	isWin    =  ( (uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") !=- 1) );
	isMac    = ( (uagent.indexOf("mac") != -1) || (navigator.vendor == "Apple Computer, Inc.") );
	uaVers   = parseInt(navigator.appVersion);
var showpage = lang_g['g_intm'];
function get_cookie( name ) {
	cname = cookie_id + name + '=';
	cpos  = document.cookie.indexOf( cname );
	if ( cpos != -1 ) {
		cstart = cpos + cname.length;
		cend   = document.cookie.indexOf(";", cstart);
		if (cend == -1) {
			cend = document.cookie.length;
		}
		return unescape( document.cookie.substring(cstart, cend) );
	}
	return null;
}
function set_cookie( name, value, cookiedate ) {
	expire = "";
	domain = "";
	path   = "/";
	if ( cookiedate ) {
		expire = "; expires=Wed, 1 Jan 2020 00:00:00 GMT";
	}
	if ( cookie_domain )	{
		domain = '; domain=' + cookie_domain;
	}
	if ( cookie_path ) 	{
		path = cookie_path;
	}
	document.cookie = cookie_id + name + "=" + value + "; path=" + path + expire + domain + ';';
}
function multi_page_jump( url_bit, totalposts, perpage ) {
	pages = 1;
	cur_pp = current_page;
	curpage  = 1;
	if ( totalposts % perpage == 0 ) {
		pages = totalposts / perpage;
	} else 	{
		pages = Math.ceil( totalposts / perpage );
	}
	msg = showpage + pages;
	if ( cur_pp > 0 ) {
		curpage = cur_pp / perpage;
		curpage = curpage -1;
	}
	show_page = 1;
	if ( curpage < pages ) {
		show_page = curpage + 1;
	}
	if ( curpage >= pages ) {
		show_page = curpage - 1;
	} else 	{
 		show_page = curpage + 1;
 	}
	userPage = prompt( msg, show_page );
	if ( userPage > 0  ) {
		if ( userPage < 1 ) {
			userPage = 1;
		}
		if ( userPage > pages ) { 
			userPage = pages;
		}
		if ( userPage == 1 ) {
			start = 0;
		} else {
			start = (userPage - 1) * perpage;
		}
		window.location = url_bit + "&pp=" + start;
	}
}
function showHide(id1, id2) {
	if (id1 != '') toggleview(id1);
	if (id2 != '') toggleview(id2);
}
function my_getbyid(id) {
	itm = null;
	if (document.getElementById) {
		itm = document.getElementById(id);
	} else if (document.all)	{
		itm = document.all[id];
	} else if (document.layers) {
		itm = document.layers[id];
	}
	return itm;
}
function toggleview(id) {
	if ( ! id ) return;
	if ( itm = my_getbyid(id) ) {
		if (itm.style.display == "none") {
			my_show_div(itm);
		} else {
			my_hide_div(itm);
		}
	}
}
function my_hide_div(id) {
	if ( ! id ) return;
	id.style.display = "none";
}
function my_show_div(id) {
	if ( ! id ) return;
	id.style.display = "";
}
function change_cell_color( id, cl ) {
	itm = my_getbyid(id);
	if ( itm )	{
		itm.className = cl;
	}
}
function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
	showx = "";
	showy = "";
	if (posleft != 0) { X = posleft }
	if (postop  != 0) { Y = postop  }
	if (!scroll) { scroll = 1 }
	if (!resize) { resize = 1 }
	if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
		X = (screen.width  - width ) / 2;
		Y = (screen.height - height) / 2;
	}
	if ( X > 0 )	{ showx = ',left='+X; }
	if ( Y > 0 )	{ showy = ',top='+Y; }
	if (scroll != 0) { scroll = 1 }
	var Win = window.open( url, name, 'width='+width+',height='+height+ showx + showy + ',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
}
function stacksize(thearray) {
	for (i = 0 ; i < thearray.length; i++ ) {
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) return i;
	}
	return thearray.length;
}
function pushstack(thearray, newval) {
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
}
function popstack(thearray) {
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
}
function CheckAll(fmobj) {
	for (var i=0;i<fmobj.elements.length;i++) {
		var e = fmobj.elements[i];
		if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
			e.checked = fmobj.allbox.checked;
		}
	}
}
function HighlightAll(str) {
    if (document.all){
        var rng = document.body.createTextRange();
        rng.moveToElementText(str);
        rng.scrollIntoView();
        rng.select();
        rng.execCommand("Copy");
        rng.collapse(false);
		setTimeout("window.status=''",1800);
    }
}
function redirlocate(object) {
	if(object.options[object.selectedIndex].value != '') {
		window.location = (object.options[object.selectedIndex].value);
	}
}
function turnAjax(){
	var closeajax = get_cookie('closeajax');
	var tabAjax = my_getbyid('isajax');
	if (isOpera == true)
	{
		alert(lang_g['g_error']);
		return false;
	}
	if (!closeajax && typeof closeajax != 'null'){
		set_cookie('closeajax',"1");
		tabAjax.innerHTML = "<input type='button' value=' "+lang_g['g_open']+" ' class='button' onclick='turnAjax()' />";
		return;
	} else { 
		if (closeajax == '1') {
			set_cookie('closeajax',"0");
			tabAjax.innerHTML = "<input type='button' value=' "+lang_g['g_close']+" ' class='button' onclick='turnAjax()' />";
			return;
		}
		set_cookie('closeajax',"1");
		tabAjax.innerHTML = "<input type='button' value=' "+lang_g['g_open']+" ' class='button' onclick='turnAjax()' />";
		return;
	}
}
function changeMod(Mode){
	if (Mode == 2) return false;
	else if (Mode == 0) set_cookie('mxeditor',"bbcode");
	else set_cookie('mxeditor',"wysiwyg");
	var div_mxeditor = my_getbyid('mxeditorinfo');
	div_mxeditor.innerHTML = lang_g['g_edv'];
}
function SelectTag(){
	var target = my_getbyid('selectall').checked;
	if (target == true)
	{
		SelectAll();
	} else {
		NoneAll();
	}
}
function SelectAll(){
	var rows = document.modform.getElementsByTagName('tr');
	var unique_id;
	var marked_row = new Array;
    var checkbox;
	for ( var i = 0; i < rows.length; i++ ) {
        checkbox = rows[i].getElementsByTagName( 'input' )[0];

        if ( checkbox && checkbox.type == 'checkbox' ) {
            unique_id = checkbox.name + checkbox.value;
            if ( checkbox.disabled == false ) {
                checkbox.checked = true;
                if ( typeof(marked_row[unique_id]) == 'undefined' || !marked_row[unique_id] ) {
                    rows[i].className += ' marked';
                    marked_row[unique_id] = true;
                }
            }
	    }
	}
}
function NoneAll(){
	var rows = document.modform.getElementsByTagName('tr');
    var unique_id;
    var checkbox;
	var marked_row = new Array;
	for ( var i = 0; i < rows.length; i++ ) {

        checkbox = rows[i].getElementsByTagName( 'input' )[0];

        if ( checkbox && checkbox.type == 'checkbox' ) {
            unique_id = checkbox.name + checkbox.value;
            checkbox.checked = false;
            rows[i].className = rows[i].className.replace(' marked', '');
            marked_row[unique_id] = false;
        }
	}

	return true;
}
function check_e_client(imgurl){
	var email = my_getbyid("email").value;
	var emailconfirm = my_getbyid("emailconfirm").value;
	if (email != emailconfirm) {
		my_getbyid("isok_email_firm").innerHTML = "<span style='color:red;position:absolute;'><img src='./images/"+imgurl+"/note_error.gif' /> "+lang_g['g_check_email']+"</span>";
	} else {
		my_getbyid("isok_email_firm").innerHTML = "<span style='color:red;position:absolute;'><img src='./images/"+imgurl+"/note_ok.gif' /></span>";
	}
}
function check_p_client(imgurl){
	var password = my_getbyid('password').value;
	var passwordconfirm = my_getbyid('passwordconfirm').value;
	if (password == '' || password != passwordconfirm) {
		my_getbyid("isok_password").innerHTML = "<span style='color:red;position:absolute;'><img src='./images/"+imgurl+"/note_error.gif' /> "+lang_g['g_check_password']+"</span>";
	} else {
		my_getbyid("isok_password").innerHTML = "<span style='color:red;position:absolute;'><img src='./images/"+imgurl+"/note_ok.gif' /></span>";
	}
}
function calculate_byte( sTargetStr ) {
	if (typeof(wMode) != 'undefined' && wMode)
	{
		sTargetStr = sTargetStr.replace(/<img( ||.*?)smilietext=('|\"|)(.*?)('|\"|>| )(.*?)>/gi, "$3");
		sTargetStr = sTargetStr.replace(/<img( ||.*?)src=('|\"|)(.*?)('|\"|>| )(.*?)>/gi, "[img]$3[/img]");
		sTargetStr = sTargetStr.replace(/<[\/\!]*?[^<>]*?>/g, '');
		sTargetStr = sTargetStr.replace(/&amp;/g, '1');
		sTargetStr = sTargetStr.replace(/&lt;/g, '1');
		sTargetStr = sTargetStr.replace(/&gt;/g, '1');
	}
	return sTargetStr.length;
}