function is_number(str)
{
	exp=/[^0-9()-]/g;
	if(str.search(exp) != -1)
	{
		return false;
	}
	return true;
}
function is_email(str)
{ if((str.indexOf("@")==-1)||(str.indexOf(".")==-1)){
	
	return false;
	}
	return true;
}

function CheckInput(){

	if(form1.username.value==''){
		alert("��û����д�ǳƣ�");
		form1.username.focus();
		return false;
	}
	if(form1.username.value.length>20){
		alert("�ǳƲ��ܳ���20���ַ���");
		form1.username.focus();
		return false;
	}

	if(!is_number(document.form1.qq.value)){
		alert("QQ������������֣�");
		form1.qq.focus();
		return false;
	}

	if( form1.email.value =="") {
                alert("����������E-mail !")
		form1.email.focus();
        return false;
    }

	if(!is_email(form1.email.value))
	{ alert("�Ƿ���EMail��ַ��");
		form1.email.focus();
	return false;
	}

	if(form1.title.value==''){
		alert("��û����д���Ա��⣡");
		form1.title.focus();
		return false;
	}
	if(form1.title.value.length>20){
		alert("���Ա��ⲻ�ܳ���20���ַ���");
		form1.title.focus();
		return false;
	}

	if(form1.content.value==''){
		alert("��û����д�������ݣ�");
		form1.content.focus();
		return false;
	}
	if(form1.content.value.length>255){
		alert("�������ݳ���255���ַ���");
		form1.content.focus();
		return false;
	}
	
	return true;
}