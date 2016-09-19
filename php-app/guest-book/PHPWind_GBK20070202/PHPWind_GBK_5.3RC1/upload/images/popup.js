timePopup=5;
var ns=(document.layers);
var ie=(document.all);
var w3=(document.getElementById && !ie);
adCount=0;
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
	if(adCount<timePopup*10){
		adCount+=1;
		if (ie){
			documentWidth  =ietruebody().offsetWidth/2+ietruebody().scrollLeft-20;
			documentHeight =ietruebody().offsetHeight/2+ietruebody().scrollTop-20;
		} else if (ns){
			documentWidth=window.innerWidth/2+window.pageXOffset-20;
			documentHeight=window.innerHeight/2+window.pageYOffset-20;
		} else if (w3){
			documentWidth=self.innerWidth/2+window.pageXOffset-20;
			documentHeight=self.innerHeight/2+window.pageYOffset-20;
		}
		adDiv.left=documentWidth-250 + 'px';
		adDiv.top =documentHeight-150 + 'px';
		setTimeout("showPopup()",100);
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
function ietruebody(){
	return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
} 
onload=initPopup;