function agree_terms() {
	if (document.terms.agree_to_terms.value === "") 	{
		alert ( mustagreeterms );
		return false;
	}
}
function validate() {
	if (document.REG.username.value == "" || document.REG.password.value == "" || document.REG.passwordconfirm.value == "" || document.REG.email.value == "") {
		alert ( inputallform );
		return false;
	}
}
function urlcheckuser() {
	var name = document.REG.username.value;
	window.open ("register.php?do=checkname&name="+escape(name)+"", "newwindow", "height=100, width=400, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, status=no");
}
function urlcheckmail() {
	var email = document.REG.email.value;
	window.open ("register.php?do=checkemail&email="+escape(email)+"", "newwindow", "height=100, width=400, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, status=no");
}