var cookie_name;
getObj = function(id) {
	return document.getElementById(id);
}
function findPosX(obj){
	var curleft = 0;
	if (obj.offsetParent){
		while (obj.offsetParent){
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft - document.body.scrollLeft;
}
function findPosY(obj){
	var curtop = 0;
	if (obj.offsetParent){
		while (obj.offsetParent){
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}else if (obj.y){
		curtop += obj.y;
	}
	return curtop - document.body.scrollTop;;
}
function click_open(idName,object){
	if( cookie_name == 1) {   
	    cookie_name = 0;
		closep();
		return false;
	}
	cookie_name = 1;
	mouseover_open(idName,object);
}
function mouseover_open(idName,object){
	if( cookie_name == 1) {
		obj = getObj("showmenu");
		obj2 = getObj(idName);
		obj3 = getObj(object);

		obj.innerHTML	= obj2.innerHTML;		
		obj.style.top	= findPosY(obj3)+15;
		obj.style.left	= findPosX(obj3);
        obj.className	= obj2.className;
		obj.style.display	= "";

		document.onmousedown = doc_mousedown;
		return false;
	}
	return false;
}
function closep(){
	obj = getObj("showmenu");
	obj.innerHTML = "";
	obj.className = "";
	obj.style.display = "none";
	document.onmousedown = function(){};
	return false;
}
function doc_mousedown(){
	obj	= getObj("showmenu");
	_x	= event.x;
	_y	= event.y;
	_x1 = obj.offsetLeft;
	_x2 = obj.offsetLeft + obj.offsetWidth;
	_y1 = obj.offsetTop - 25;
	_y2 = obj.offsetTop + obj.offsetHeight;
	if ((_x>_x1)&&(_x<_x2)&&(_y>_y1)&&(_y<_y2)){
		document.onmousedown = doc_mousedown;
	}else{
	    cookie_name = 0;
		closep();
	}
}
cookie_name = 0;