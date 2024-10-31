/**
 * Package main JavaScript
 *
 * @package myPluginSafeUpgrade
 * @author Ugo Grandolini
 * @version 0.0.3
 * @since 0.0.2
 */

var mpsuURL = encodeURI('<?php echo $_GET['u']; ?>/wp-admin/tools.php?page=myPluginSafeUpgrade_tools');
var mpsuTEXT = '<?php echo $_GET['m']; ?>';

function check_update_info() {
	//
	//	let's look for wordpress update messages
	//
	var el = document.getElementById('footer');
	if(!el)
	{
		//	the browser has not yet rendered the footer
		//
		setTimeout('check_update_info()', 500);
		return false;
	}

	//
	//	gets all the page links
	//
	var update_link = document.getElementsByTagName('a');

	for(i=0;i<update_link.length;i++)
	{
		//	check each link on the page to see if it points to the upgrade page
		//
		if(update_link[i].href.indexOf('update-core.php')>=0					//	this is a link to the upgrade page (WP >= 2.7)
			&& location.href.indexOf('update-core.php')<0						//	we are not on the update page
			&& location.href.indexOf('myPluginSafeUpgrade_tools')<0				//	we are not on the plugin report page
			|| update_link[i].href.indexOf('http://wordpress.org/download/')>=0	// 0.0.3 (WP 2.5, 2.6)
		) {
			//	add a reminder to the check versions page
			//
			var element = document.createElement('div');
				element.style.padding = '6px';
				//element.style.color = '#000';
				element.style.backgroundColor = '#FEFF9F';

				var myLink = document.createElement('a');
				var href = document.createAttribute('href');
					myLink.setAttribute('href', mpsuURL);
					myLink.innerHTML = mpsuTEXT;

				element.appendChild(myLink);

			update_link[i].parentNode.appendChild(element);
		}
	}
}

//	give the browser half a second to draw the page
//
setTimeout('check_update_info()', 500);
