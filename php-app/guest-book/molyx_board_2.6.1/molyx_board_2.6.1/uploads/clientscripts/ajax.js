		var mxajax_debug_mode = false;
		
		function mxajax_debug(text) {
			if (mxajax_debug_mode) {
				alert("RSD: " + text);
			}
		}
		function mxajax_init_object() {
			mxajax_debug("mxajax_init_object() called..");
			
			var RetValue;
			try {
				RetValue = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					RetValue = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (oc) {
					RetValue = null;
				}
			}
			if(!RetValue && typeof XMLHttpRequest != "undefined") {
				RetValue = new XMLHttpRequest();
			}
			if (!RetValue) {
				mxajax_debug("Could not create connection object.");
			}
			return RetValue;
		}

		function mxajax_run(func_name, args) {
			var i, x, n;
			var uri;
			var post_data;
			
			uri = "./mxajax.php";
			if (sessionid)
			{
				uri = uri + '?s=' + sessionid;
			}
			if (mxajax_request_type == "GET") {
				uri = uri + "&rs=" + func_name;
				for (i = 0; i < args.length-1; i++) {
					uri = uri + "&rsargs[]=" + args[i];
				}
				uri = uri + "&rsrnd=" + new Date().getTime();
				post_data = null;
			} else {
				post_data = "rs=" + func_name;
				for (i = 0; i < args.length-1; i++) {
					post_data = post_data + "&rsargs[]=" + urlencode(args[i]);
				}
			}
			
			x = mxajax_init_object();
			x.open(mxajax_request_type, uri, true);
			if (mxajax_request_type == "POST") {
				x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
				x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			}
			x.onreadystatechange = function() {
				if (x.readyState != 4) {
					return;
				}
				mxajax_debug("received " + x.responseText);
				var status;
				var data;
				status = x.responseText.charAt(0);
				datacache = x.responseText.substring(0);
				data = unescape(datacache);
				if (status == "-") {
					alert("Error: " + data);
				} else {
					args[args.length-1](data);
				}
			};
			x.send(post_data);
			mxajax_debug(func_name + " uri = " + uri + "/post = " + post_data);
			mxajax_debug(func_name + " waiting..");
			delete x;
		}
		
		function x_changetext() {
			mxajax_run("changetext", x_changetext.arguments);
		}
		function x_change_value() {
			mxajax_run("change_value", x_change_value.arguments);
		}
		function x_returnsig() {
			mxajax_run("returnsig", x_returnsig.arguments);
		}
		function x_rep() {
			mxajax_run("rep", x_rep.arguments);
		}
		function x_quickreply() {
			mxajax_run("quickreply", x_quickreply.arguments);
		}
		function x_returntext() {
			mxajax_run("returntext", x_returntext.arguments);
		}
		function x_switchtext() {
			mxajax_run("switchtext", x_switchtext.arguments);
		}
		function x_checkuser() {
			mxajax_run("checkuser",	x_checkuser.arguments);
		}
		function x_checkmail() {
			mxajax_run("checkmail", x_checkmail.arguments);
		}
		function x_change_name() {
			mxajax_run("change_name", x_change_name.arguments);
		}
		function x_openthread() {
			mxajax_run("openthread", x_openthread.arguments);
		}
		function x_closethread() {
			mxajax_run("closethread", x_closethread.arguments);
		}
		function x_cbsend() {
			mxajax_run("cbsend", x_cbsend.arguments);
		}
		function x_sendattach() {
			mxajax_run("sendattach", x_sendattach.arguments);
		}
		function x_removeattach() {
			mxajax_run("removeattach", x_removeattach.arguments);
		}
		function x_returnpagetext() {
			mxajax_run("returnpagetext", x_returnpagetext.arguments);
		}
		function x_deletepost() {
			mxajax_run("deletepost", x_deletepost.arguments);
		}
		function x_changerule() {
			mxajax_run("changerule", x_changerule.arguments);
		}
		function x_change_cash() {
			mxajax_run("change_cash", x_change_cash.arguments);
		}
		function x_sendpreview() {
			mxajax_run("sendpreview", x_sendpreview.arguments);
		}
		function x_smilespage() {
			mxajax_run("smilespage", x_smilespage.arguments);
		}