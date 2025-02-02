/**
 * AJAX caller
 *
 * @package myPluginSafeUpgrade
 * @author Ugo Grandolini
 * @version 0.0.1
 */

//
//	http://rajshekhar.net/blog/archives/85-Rasmus-30-second-AJAX-Tutorial.html
//	http://www.openjs.com/articles/ajax_xmlhttp_using_post.php
//
//alert('ajax_ro.js');
var ajax_ro_item = '';	// 09/02/2009

//---------------------------------------
function createRequestObject() {
//---------------------------------------
	var ro;
	ro = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	return ro;
/*	var ro;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		ro = new ActiveXObject("Microsoft.XMLHTTP");//alert('ie');
	}else{
		ro = new XMLHttpRequest();//alert('nonie');
	}
	return ro;
	*/
}

var http = createRequestObject();

//---------------------------------------
function sndReq(action,tag,parms)
//---------------------------------------
{
	// 09/02/2009: BEG
	if(ajax_ro_item!='')
	{
		//	ajax is already running...
		//
//		alert(ajax_ro_item+' sndReq(action:'+action+', tag:'+tag+', parms:'+parms+');');
		setTimeout(
			'sndReq("'+action+'","'+tag+'","'+parms+'");'
			, 500);
		return;
	}
	ajax_ro_item = action;
	// 09/02/2009: END

	//var url = '/wordpress-2.9.1/wp-content/plugins/myeasydb/' + 'ajax_ro.php';
	var url = ajaxURL;

	var vars = 'action='+action+'&tag='+tag+'&parms='+parms;

//alert('url = '+url);

/*
//	using GET
//	http.open('get', 'skins/progeSOFT/ajax_ro.php?action='+action+'&tag='+tag+'&parms='+parms);
//	http.open('get', url+'?'+vars);

//	using POST
	http.open('post', url, true);
	http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	http.setRequestHeader('Content-length', vars.length);
	http.setRequestHeader('Connection', 'close');

	http.onreadystatechange = handleResponse;

//	http.send(null);	// GET
	http.send(vars);	// POST
*/

/////
/////
/////
	//var servertype = getCookie('servertype');	//	10/06/2009
	var servertype = '';
/////
/////
/////
	switch(servertype)
	{
		case 'production':
			//
			// using GET
			//
			http.open('get', url+'?'+vars);
			break;
			//
		default:
			//
			// using POST
			//
			http.open('post', url, true);
			http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			//http.setRequestHeader('Content-length', vars.length);	# 11/01/2010
			//http.setRequestHeader('Connection', 'close');			# 11/01/2010
	}

	http.onreadystatechange = handleResponse;

	switch(servertype)
	{
		case 'production':
			http.send(null);
			break;
			//
		default:
			http.send(vars);
	}
}
//---------------------------------------
function handleResponse()
//---------------------------------------
{
	if(http.readyState == 4)
	{
		var response = http.responseText;

		var blocks = new Array();
		var update = new Array();
		var cmd    = new Array();
		var i = 0;

		var splitter_tag   = '|-ajax-tag-|';
		var splitter_block = '|-ajax-block-|';
		var splitter_cmd   = '|-ajax-cmd-|';

//alert("readyState(4):response\n"+response);
if(response=='' || typeof response=='undefined' || response==null)		//	16/05/2009
{
	return;
}

		//
		//	newdiv => http://domscripting.com/blog/display/99
		//
		var newdiv = document.createElement('ajax');

		if(response.indexOf(splitter_block) != -1)
		{
			blocks = response.split(splitter_block);
			update = blocks[0].split(splitter_tag);
			cmd    = blocks[1].split(splitter_cmd);
		}
		else
		{
			update = response.split(splitter_tag);
			cmd    = 0;
		}

		//
		//	handle the tag updates
		//
//		if(response.indexOf(splitter_tag) != -1)
//		{
////		update = response.split(splitter_tag);
			newdiv.innerHTML = update[1];

			var ready = document.getElementById(update[0]);

//alert("readyState(4):update\n"+update);
//alert(response+"\n[0]:"+update[0]+"\n[1]:"+update[1]);

			if(typeof ready != 'undefined' && ready != null)
			{
				// if the dom is ready let's update it
				//
				document.getElementById(update[0]).innerHTML = '';
				document.getElementById(update[0]).appendChild(newdiv);
			}
			else
			{
				// if the dom is NOT ready let's wait 200ms. then update it
				//

//alert('elId:'+elId+', elCont:'+elCont);

				if(typeof update[0]!='undefined' && typeof update[1]!='undefined'
					&& update[0]!='' && update[1]!='')
				{
					var elId = update[0];
					var elCont = update[1].replace(/\"/g, '@');
					setTimeout('delayUpdate("'+elId+'","'+elCont+'")', 200);
				}

//				var check = document.getElementById(elId);
//				if(typeof check != 'undefined' && check != null)
//				{
//					setTimeout(
//						'document.getElementById("'+elId+'").innerHTML = \'\';'+
//						'var newdiv = document.createElement(\'ajax\');'+
//						'newdiv.innerHTML = \''+elCont+'\';'+
//						'document.getElementById("'+elId+'").appendChild(newdiv);'
//						, 200);
//				}
			}
//		}

		//
		//	executes the required javascript commands
		//
		if(cmd != 0)
		{
			var t = cmd.length;
			for(i=0;i<t;i++)
			{
//				alert('cmd['+i+']='+cmd[i]);
				eval(cmd[i]);
			}
		}

		ajax_ro_item = '';	// 09/02/2009

/*		var i = 0, t = blocks.length;
		for(i=0;i<t;i++)
		{
//			if(response.indexOf(splitter_tag) != -1)
			if(blocks[i].indexOf(splitter_tag) != -1)
			{
//				update = response.split(splitter_tag);
				update = blocks[i].split(splitter_tag);
				newdiv.innerHTML += update[1];
			}
		} */

	}
}
//---------------------------------------
function delayUpdate(elId,elCont)
//---------------------------------------
{
	var check = document.getElementById(elId);
	if(typeof check != 'undefined' && check != null)
	{
		var elCont = elCont.replace(/@/g, '"');
		setTimeout(
			'document.getElementById("'+elId+'").innerHTML = \'\';'+
			'var newdiv = document.createElement(\'ajax\');'+
			'newdiv.innerHTML = \''+elCont+'\';'+
			'document.getElementById("'+elId+'").appendChild(newdiv);'
			, 50);
	}
}
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
