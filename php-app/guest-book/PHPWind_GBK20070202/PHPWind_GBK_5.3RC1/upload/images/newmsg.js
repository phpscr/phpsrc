var timePopup=60;
var ns=(document.layers);
var ie=(document.all);
var w3=(document.getElementById && !ie);
var adCount=0;
var hdCount=0;
var iTime;
function initPopup(){
	if(!ns && !ie && !w3){
		return;
	}
	if(ie){
		adDiv=eval('document.all.windlocation.style');
	}else if(ns){
		adDiv=eval('document.layers["windlocation"]');
	}else if(w3){
		adDiv=eval('document.getElementById("windlocation").style');
	}
	if (ie||w3){
		adDiv.visibility="visible";
	}else{
		adDiv.visibility ="show";
	}
	showPopup();
}
function showPopup(){
	if (ie){
		documentWidth  =ietruebody().offsetWidth;
		documentHeight =ietruebody().offsetHeight+ietruebody().scrollTop-90;
	} else if (ns){	
		documentWidth  =window.innerWidth;
		documentHeight =window.innerHeight+ietruebody().scrollTop-90;
	} else if (w3){
		documentWidth  =self.innerWidth;
		documentHeight =self.innerHeight+ietruebody().scrollTop-90;
	}
	adDiv.left=documentWidth-220 + 'px';

	if(adCount < 10){
		adCount++;
		adDiv.top = documentHeight+80-adCount*8 + 'px';
		iTime = setTimeout("showPopup()",100);
	}else if(adCount < timePopup-10){
		adCount++;
		adDiv.top = documentHeight + 'px';
		iTime = setTimeout("showPopup()",100);
	}else if(adCount < timePopup){
		adCount++;
		hdCount++;
		adDiv.top = documentHeight+hdCount*8 + 'px';
		iTime = setTimeout("showPopup()",100);
	}else{
		closePopup();
	}
}
function closePopup(){
	if (ie||w3){
		adDiv.display="none";
	}else{
		adDiv.visibility ="hide";
	}
}
function clearpop(){
	clearTimeout(iTime);
}
function hide(){
	closePopup();
	var date = new Date();
	date.setTime(date.getTime()+86400000);
	document.cookie="msghide=1;expires=" + date.toGMTString() + " path=/";
} 
function ietruebody(){
	return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
} 
onload=initPopup;