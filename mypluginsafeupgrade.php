<?php
/*
Plugin Name: myEASY myPluginSafeUpgrade
Plugin URI: http://myeasywp.com
Description: Check if the plugins you have installed are compatible with the new WordPress version with one click.
Version: 0.0.4
Author: Ugo Grandolini aka "camaleo"
Author URI: http://myeasywp.com
*/
/*	Check if the plugins you have installed are compatible with the new WordPress version with one click.
	Copyright (C) 2010 Ugo Grandolini  (email : info@myeasywp.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/

define('MPSU_PATH', dirname(__FILE__) . '/');

$myPluginSafeUpgrade_dir = basename(dirname(__FILE__));

#
#	0.0.4: BEG
#-------------
session_start();

if(!isset($_SESSION['MPSU_CURRENT_WP']))
{
	$tmp = get_transient('update_core');
	$_SESSION['MPSU_CURRENT_WP'] = $tmp->updates[0]->current;
}
#-------------
#	0.0.4: END
#


#
#	link to the plugin folder (eg. http://example.com/wordpress-2.9.1/wp-content/plugins/MyPlugin/)
#
define(MYPLUGINSAFEUPGRADE_LINK, get_option('siteurl').'/wp-content/plugins/' . $myPluginSafeUpgrade_dir . '/');

define('MPSU_LOCALE', 'myPluginSafeUpgrade');	#	the locale for translations




#
#	hook for adding admin menus
#
add_action('admin_menu', 'myPluginSafeUpgrade_add_pages');

load_plugin_textdomain( MPSU_LOCALE, 'wp-content/plugins/' . $myPluginSafeUpgrade_dir, $myPluginSafeUpgrade_dir );

wp_enqueue_script( 'myeasywp_ajax_js', MYPLUGINSAFEUPGRADE_LINK.'js/ajax_ro.js', '', '20100123', false );
wp_enqueue_script( 'mpsu_js', MYPLUGINSAFEUPGRADE_LINK.'js/mpsu.js.php?u='.get_option('siteurl').'&m='.__('CHECK THE PLUGINS VERSION BEFORE THE UPGRADE!', MPSU_LOCALE), '', '20100126', false );

#
#	action function for above hook
#
function myPluginSafeUpgrade_add_pages() {
	#
	#	Add the main page
	#
	add_management_page(__( 'myPluginSafeUpgrade', MPSU_LOCALE ), __( 'myPluginSafeUpgrade', MPSU_LOCALE ), 'administrator', 'myPluginSafeUpgrade_tools', 'myPluginSafeUpgrade_tools_page');
}

function myPluginSafeUpgrade_tools_page() {
	#
	#	Results page
	#
	echo '<div class="wrap">'
			.'<div id="icon-options-general" class="icon32"><br /></div>'
			.'<h2>myPluginSafeUpgrade: ' . __( 'Results page', MPSU_LOCALE ) . '</h2>'
	;

	?>
	<div id="check_results">
		<img src="http://myeasywp.com/common/img/loading.gif" />
	</div>

	<script type="text/javascript">
	var ajaxURL = window.location.protocol + '//' + window.location.hostname + '/<?php

		echo str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) . '/';

	?>ajax_ro.php';

	var imgURL = window.location.protocol + '//' + window.location.hostname + '/<?php

		echo str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) . '/';

	?>img/';
		sndReq('get_mpsu_checks','check_results','');
	</script><?php
}

?>