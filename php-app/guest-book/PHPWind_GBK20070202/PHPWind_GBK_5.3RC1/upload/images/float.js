/*
���µ������
*/
var showtime=0;
var marginL=10; //�ұ߾�
var it1;

function ShowPop(){
	var popup=document.createElement("DIV");
	popup.id="popup";
	popup.className='t';
	popup.style.height=popHeight+"px";
	popup.style.width=popWidth+"px";
	popup.style.padding="0";
	popup.style.margin="0";
	popup.style.backgroundColor="#FFFFFF";
	popup.style.position="absolute";
	popup.style.top=ietruebody().clientHeight+ietruebody().scrollTop-marginL-popHeight+"px";
	popup.style.right=marginL+"px";
	popup.innerHTML="<table cellspacing=0 cellpadding=0 width=100%><tr><td class='h'><span style='float:right;'>"+popTitle+"<a id='closeButton' title='close' onclick='hidePop();'>X</a></span></td></tr><tr class='f_one'><td style='padding:5px;'>" + popCode + "</td></tr></table>";
	var btn = findElement(popup,'a',"closeButton");
	btn.style.cssText='cursor:pointer;padding:1px 2px 1px;height:8px;width:8px;margin-left:5px;border:1px solid #9ad; font:9px Verdana; text-decoration:none;';
	
	document.body.appendChild(popup);
	it1 = setInterval("floatPop()",100);
}
function floatPop(){
	if(autoClose>0){
		showtime++;
		if(showtime > autoClose*10){
			hidePop('auto');
			return;
		}
	}
	document.getElementById("popup").style.top=ietruebody().clientHeight+ietruebody().scrollTop-marginL-popHeight+"px";
}
function hidePop(type){
	document.getElementById("popup").style.display="none";
	clearInterval(it1);
	if(typeof type=='undefined') document.cookie="hidepop=1; path=/";
}
function findElement(root,tag,id){
	var ar=root.getElementsByTagName(tag);
	for(var i=0;i<ar.length;i++){
		if(ar[i].id==id) return ar[i];
	}
	return null;
}

/*
Ư�����
*/
var it2;
var delay = 10;
var x = 50,y = 60; //��ʼ����
var xin = true,yin = true;
var step = 1;

function ShowAd(){
	document.write("<div id='floatAd' style='position:absolute'>");
	document.write(floatCode);
	document.write("<br /><a style='cursor:pointer;' onclick='hideAd();'>�ر�</a></div>");
	obj = document.getElementById("floatAd");
	it2= setInterval("floatAd()", delay);
	obj.onmouseover=function(){clearInterval(it2)};
	obj.onmouseout=function(){it2=setInterval("floatAd()", delay)};
}
function floatAd(){
	var L=T=0;
	var R = ietruebody().clientWidth-obj.offsetWidth;
	var B = ietruebody().clientHeight-obj.offsetHeight;
	obj = document.getElementById("floatAd");
	obj.style.left = x + ietruebody().scrollLeft + "px";
	obj.style.top = y + ietruebody().scrollTop + "px";
	x = x + step*(xin?1:-1);
	if (x < L) { xin = true; x = L};
	if (x > R) { xin = false; x = R};
	y = y + step*(yin?1:-1);
	if (y < T) { yin = true; y = T };
	if (y > B) { yin = false; y = B };
}
function hideAd(){
	document.getElementById("floatAd").style.display="none";
	clearInterval(it2);
}

/*
����Ư���������
*/
var marginTop = 120; //�Զ��ϱ߾�
var marginX = 15; //���� �߾�
var it3;

function ShowFloat(){
	if(LeftCode!='')
		document.write("<div id=\"adLeftFloat\" style=\"position: absolute; left:"+marginX+"px; top:"+marginTop+"px;\"><a href=\"" + LeftHref +"\">"+LeftCode+"</a><br><div style=\"width:100;background-color:#E1E1E1; text-align:left\"><a style=\"cursor:pointer;\" onclick=\"hideFloat();\">�ر�</a></div></div>");
	if(RightCode!='')
		document.write("<div id=\"adRightFloat\" style=\"position: absolute; right:"+marginX+"px; top:"+marginTop+"px;\"><a href=\"" + RightHref +"\">"+ RightCode +"</a><br><div style=\"width:100;background-color:#E1E1E1; text-align:right\"><a style=\"cursor:pointer;\" onclick=\"hideFloat();\">�ر�</a></div></div>");
	moveFloat();
}
function hideFloat(){
	clearTimeout(it3);
	if(IsElement("adLeftFloat"))
		document.getElementById("adLeftFloat").style.display = "none";
	if(IsElement("adRightFloat"))
		document.getElementById("adRightFloat").style.display = "none";
	return false;
}
function moveFloat(){
	if(IsElement("adLeftFloat"))
		document.getElementById("adLeftFloat").style.top = ietruebody().scrollTop + marginTop + 'px';
	if(IsElement("adRightFloat"))
		document.getElementById("adRightFloat").style.top = ietruebody().scrollTop + marginTop + 'px';
	it3 = setTimeout("moveFloat();",80);
}