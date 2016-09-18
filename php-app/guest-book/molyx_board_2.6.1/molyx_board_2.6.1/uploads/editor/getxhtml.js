// === fix html2xhtml
var re_comment = new RegExp();
re_comment.compile("^<!--(.*)-->$");
var re_hyphen = new RegExp();
re_hyphen.compile("-$");

function get_xhtml(node) {
	var i;
	var text = '';
	var children = node.childNodes;
	var child_length = children.length;
	var tag_name;
	
	for (i = 0; i < child_length; i++) {
		var child = children[i];

		switch (child.nodeType) {
			case 1: { //ELEMENT_NODE
				var tag_name = String(child.tagName).toLowerCase();
				if (tag_name == '' || tag_name == 'style' || tag_name == 'title' || tag_name == 'script' || tag_name == 'iframe') break;
				if (tag_name == '!') { //COMMENT_NODE
					var parts = re_comment.exec(child.text);
					if (parts) {
						var inner_text = parts[1];
						text += fix_comment(inner_text);
					}
				} else {
					text += '<'+tag_name;
					var attr = child.attributes;
					var attr_length = attr.length;
					var attr_value;
					
					var attr_lang = false;
					var attr_xml_lang = false;
					var attr_xmlns = false;
					
					var is_alt_attr = false;
					
					for (j = 0; j < attr_length; j++) {
						var attr_name = attr[j].nodeName.toLowerCase();
						
						if (!attr[j].specified && 
							(attr_name != 'selected' || !child.selected) && 
							(attr_name != 'style' || child.style.cssText == '') && 
							attr_name != 'value') continue; //IE 5.0
						
						if (attr_name == '_moz_dirty' || 
							attr_name == '_moz_resizing' || 
							tag_name == 'br' && 
							attr_name == 'type' && 
							child.getAttribute('type') == '_moz') continue;
						
						var valid_attr = true;
						
						switch (attr_name) {
							case "style":
								attr_value = child.style.cssText;
								break;
							case "class":
								attr_value = child.className;
								break;
							case "http-equiv":
								attr_value = child.httpEquiv;
								break;
							case "noshade": break;
							case "checked": break;
							case "selected": break;
							case "multiple": break;
							case "nowrap": break;
							case "disabled": break;
								attr_value = attr_name;
								break;
							default:
								try {
									attr_value = child.getAttribute(attr_name, 2);
								} catch (e) {
									valid_attr = false;
								}
								break;
						}
						
						if (valid_attr) {
							if (!(tag_name == 'li' && attr_name == 'value')) {
								text += ' '+attr_name+'="'+fix_attribute(attr_value)+'"';
							}
						}
						if (attr_name == 'alt') is_alt_attr = true;
					}
					
					if (tag_name == 'img' && !is_alt_attr) {
						text += ' alt=""';
					}
					
					if (child.canHaveChildren || child.hasChildNodes()){
						text += '>';
						text += get_xhtml(child);
						text += '</'+tag_name+'>';
					} else {
						if (tag_name == 'style' || tag_name == 'title' || tag_name == 'script') {
							text += '></'+tag_name+'>';
						} else {
							text += ' />';
						}
					}
				}
				break;
			}
			case 3: { //TEXT_NODE
				text += fix_attribute(child.nodeValue);
				break;
			}
			case 8: { //COMMENT_NODE
				text += fix_comment(child.nodeValue);
				break;
			}
			default:
				break;
		}
	}
	
	text = text.replace(/<\/?head>[\n]*/gi, "");
	text = text.replace(/<head \/>[\n]*/gi, "");
	text = text.replace(/<\/?body>[\n]*/gi, "");
	text = text.replace("\n", "");
	return text;
}

function fix_comment(text) {
	text = text.replace(/--/g, "__");
	if(re_hyphen.exec(text)) { 
		text += " ";
	}
	return "<!--"+text+"-->";
}

function fix_attribute(text) {
	return String(text).replace(/\&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;");
}
