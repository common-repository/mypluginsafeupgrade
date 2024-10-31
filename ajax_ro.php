<?php
/**
 * AJAX responder
 *
 * @package myPluginSafeUpgrade
 * @author Ugo Grandolini
 * @version 0.0.4
 */

#-------------------------------------------------------------
#
#	first of all let's check if we are called by our server
#
#-------------------------------------------------------------
/*
//var_dump($_SERVER);

	["HTTP_REFERER"]=>string(71) "http://example.com/.../..."
	["HTTP_HOST"]=>   string(7) "example.com"
	["SERVER_NAME"]=> string(7) "example.com"

	["SERVER_ADDR"]=> string(9) "127.0.0.1"
	["REMOTE_ADDR"]=> string(9) "127.0.0.1"
*/
$tmp = explode('://', $_SERVER['HTTP_REFERER']);
$path = explode('/', $tmp[1]);
$referer = $path[0];

//echo '$tmp1['.$tmp[1].']';
//echo '$path0['.$path[0].']';
//echo '$referer['.$referer.']';

if(	($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME'])
		||
	($_SERVER['HTTP_HOST'] != $referer)
		||
	($_SERVER['SERVER_NAME'] != $referer) )
{

	echo '<div align="center">'

			.'There is an issue with the caller...'

		.'</div>'
	;
	die();
}

#
#	the caller is fine
#
define('AJAX_PARMS_SPLITTER', '|-ajax-parms-|');

session_start();											#	0.0.4


$splitter_tag	= '|-ajax-tag-|';
$splitter_block	= '|-ajax-block-|';
$splitter_cmd	= '|-ajax-cmd-|';

#
#	define needed paths and directories
#
$tmp = dirname(__FILE__);
$tmp = str_replace('\\','/', $tmp);
$tpath = explode('/',$tmp);
$t = count($tpath) - 3;
$wp_path = '';
for($i=0;$i<$t;$i++)
{
	$wp_path .= $tpath[$i] . '/';
}
define('WP_PATH', $wp_path);

#
#	initialize some variables
#
define('AJAX_CALLER', true);

/*===========================================================

	The js caller can send parameters both as GET or POST.

	POST is generally considered more sure and it also allows
	for longer parameters to be sent.

	If you like to configure the js to pass the parameters
	by GET, you need to change the $_INPUT assignment few
	lines here below.

  ===========================================================*/
//echo '$_GET:';var_dump($_GET);echo "\n\n";
//echo '$_POST:';var_dump($_POST);echo "\n\n";

//$_INPUT = $_GET;
$_INPUT = $_POST;

if(!is_array($_INPUT) || count($_INPUT)==0)
{
	#	in any case we expect parameters to be sent as an array
	#	if not, better to quit...
	#
	exit();
}

if(strpos($_INPUT['parms'], AJAX_PARMS_SPLITTER) !== false)
{
	#	if there is more than one parameter, they are separated
	#	by the constant defined in AJAX_PARMS_SPLITTER
	#
	$parms = explode(AJAX_PARMS_SPLITTER, $_INPUT['parms']);
}
else
{
	#	there is only one parameter, to keep the same logic
	#	we create an array of parameters anyway
	#
	$parms = array();
	$parms[0] = $_INPUT['parms'];
}

#
#	$parms
#
#	{n} = parameters
#
//define(AJAX_DEBUG, true);	#	uncomment to see debug code

$parms_string = '';
if(defined('AJAX_DEBUG') && AJAX_DEBUG==true)
{
	$t = count($parms);
	$parms_string = '<p class="todo">';
	for($i=0;$i<$t;$i++)
	{
		$parms_string .= '$parms['.$i.']:'.$parms[$i].'<br />';
	}
	$parms_string .= '</p>';
}
//die();

#
#	we do not want the result to be cached
#
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: nocache');
header('Expires: Fri, 31 Dec 1999 23:59:59 GMT');


echo $_INPUT['tag']		#	the tag id we are going to write to
	.$splitter_tag		#	splitter for the remaining output
	.$parms_string		#	filled only for debug purpose
;


#================================
#
#	its time to prepare some
#	output data...
#
#================================
switch($_INPUT['action'])
{
	#---------------------------
	case 'get_mpsu_checks':
	#---------------------------
		#
		#	get info about myEASYdb from its site
		#
		#	no parameters
		#
		require_once(WP_PATH.'wp-load.php');
		require_once(WP_PATH.'wp-admin/includes/plugin.php');

		$all_plugins = get_plugins();

		echo '<div id="processing" style="display:block;">' . __( 'please wait, processing...', MPSU_LOCALE ) . '<br /><br /></div>'	#	0.0.3

				.'<div id="mpsu_process"><img src="http://myeasywp.com/common/img/loading.gif" /></div>'
		;

		$js = 'sndReq(\'get_mpsu_checks_process\',\'mpsu_process\',\'';

		foreach($all_plugins as $name => $info)
		{
			$plugin_path_name = strtolower(basename($name, '.php'));			#	0.0.3
			if($plugin_path_name=='akismet' || $plugin_path_name=='hello')		#	0.0.3
			{
				#	let the admin know that the default WordPress plugins are not checked
				#
				echo '<div class="updated">'
						.'<b>'.$plugin_path_name.'</b>' . __( ': is a default WordPress plugin, thus it was not checked', MPSU_LOCALE ) . '<br />'
					.'</div>'
				;
			}
			else
			{
				$js .= $name . AJAX_PARMS_SPLITTER;
			}
		}

		$js .= '\');';

		echo $splitter_block
			.$js;

		exit();
		break;
		#
	#---------------------------
	case 'get_mpsu_checks_process':
	#---------------------------
		#
		#	0 - {n}: plugin names
		#
		require_once(WP_PATH.'wp-load.php');
		require_once(WP_PATH.'wp-admin/includes/plugin.php');

		$all_plugins = get_plugins();

		$name = $parms[0];
		$info = $all_plugins[$parms[0]];

		$plugin_path_name = strtolower(basename($name, '.php'));

		echo '<table cellspacing="3" cellpadding="4">';
		echo '<tr><td>' . __( 'Plugin name', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td><b>' . $info['Name'] . '</b></td></tr>';	#	0.0.3

		if($info['Name']!=$info['Title'])
		{
			echo '<tr><td>' . __( 'Title', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td>' . $info['Title'] . '</td></tr>';	#	0.0.3
		}

		echo ''
			.'<tr><td>' . __( 'URI', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td><a href="' . $info['PluginURI'] . '" target="_blank">' . $info['PluginURI'] . '</a></td></tr>'	#	0.0.3
			.'<tr><td>' . __( 'Latest version', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td>' . $info['Version'] . '</td></tr>'	#	0.0.3
		;

		if($info['PluginURI']!=$info['AuthorURI'] && $info['AuthorURI']!='')
		{
			echo '<tr><td>' . __( 'Author', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td><a href="'.$info['AuthorURI'].'" target="_blank">'.$info['AuthorURI'].'</a></td></tr>';	#	0.0.3
		}

		#
		#	get the plugin info
		#
		$domain = 'wordpress.org';								#	0.0.3
		$domain_path = "/extend/plugins/$plugin_path_name/";	#	0.0.3
		$needle = '<strong>Compatible up to:</strong>';			#	do NOT localize this one: its the string I am looking for to get the latest version!

		$fp = fsockopen($domain, 80, $errno, $errstr, 10);		#	0.0.3

		if (!$fp) {
			#
			#	HTTP ERROR
			#
			$compatibility = __( 'HTTP ERROR!', MPSU_LOCALE ) . ' [' . $errno . '] ' . $errstr;	#	0.0.3
			$color = 'red';

		} else {
			#
			#	get the latest version number
			#
			$header = "GET $domain_path HTTP/1.1\r\n"			#	0.0.3
						."Host: $domain_path\r\n"				#	0.0.3
						."Connection: Close\r\n\r\n"
						//."Connection: keep-alive\r\n\r\n"
			;
			fwrite($fp, $header);

			$result = '';
			while (!feof($fp)) {
				$result .= fgets($fp, 1024);
			}

			$compatibility = __( 'Unknown', MPSU_LOCALE );		#	0.0.3
			//$color = 'red';		C

			//$needle = '<strong>Compatible up to:</strong>';	#	0.0.3

			$p = strpos($result, $needle, 0);
			if($p!==false)
			{
				$beg = $p + strlen($needle) + 1;				#	0.0.4
				$end = strpos($result, '</li>', $p);
				$compatibility = substr($result, $beg, ($end-$beg));
				//$color = 'green';								#	0.0.4
			}

			#
			#	0.0.4: BEG
			#-------------
			if(version_compare($compatibility, $_SESSION['MPSU_CURRENT_WP'], '<'))
			{
				$version_compare = 'bad';
				$color = 'red';
				$_SESSION['MPSU_WARNING'] = true;
			}
			else
			{
				$version_compare = 'fine';
				$color = 'green';
			}
			#-------------
			#	0.0.4: END
			#

			fclose($fp);
		}

		echo '<tr><td>'
					. __( 'Compatible up to', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td><span style="font-weight:bold;color:' . $color . ';">'
					. $compatibility . '</span></td></tr>'	#	0.0.3
			.'<tr><td>' . __( 'Info link', MPSU_LOCALE ) . '</td><td>&nbsp;</td><td><a href="http://wordpress.org/extend/plugins/' . $plugin_path_name . '/" target="_blank">http://wordpress.org/extend/plugins/' . $plugin_path_name . '/</a></td></tr>'	#	0.0.3
			.'<tr><td colspan="99" height="6px"></td></tr>'

			.'</table>'
		;

		$js = 'sndReq(\'get_mpsu_checks_process\',\'mpsu_process_'.$parms[1].'\',\'';

		$i = 1;
		while($parms[$i]!='')
		{
			$js .= $parms[$i] . AJAX_PARMS_SPLITTER;
			$i++;
		}

		$js .= '\');';

		if($i>1)
		{
			echo '<div id="mpsu_process_'.$parms[1].'"><img src="http://myeasywp.com/common/img/loading.gif" /></div>';
		}
		else
		{
			echo '<code>* ' . __( 'All plugins checked', MPSU_LOCALE ) . ' *</code>';				#	0.0.3

			#
			#	0.0.4: BEG
			#-------------
			echo '<div>';

				if($_SESSION['MPSU_WARNING']==true)
				{
					echo '<h3 style="color:red;">'
							. __( 'Avoid to upgrade or do it at your risk!', MPSU_LOCALE )
						.'<h3>'
					;
				}
				else
				{
					echo '<h3 style="color:green;">'
							. __( 'OK to upgrade!', MPSU_LOCALE )
						.'<h3>'
					;
				}
				echo '<p>'
						. __( 'In any case, before upgrading, to be sure you can go back if anything goes wrong it would be wise to create a full backup.', MPSU_LOCALE )
						. __( 'If you do not how to backup your MySQL tables and WordPress installation, you can get the free', MPSU_LOCALE )

						. ' <a href="http://wordpress.org/extend/plugins/myeasybackup/" target="_blank"><b>myEASYbackup</b></a> '

						. __( 'plugin.', MPSU_LOCALE )
					.'</p>'
				;

			echo '</div>';
			#-------------
			#	0.0.4: END
			#

			#
			#	0.0.2: BEG
			#-------------
			echo '<h3 style="margin-bottom:0;">'.__('Donation', MPSU_LOCALE).'</h3>';
			echo __('myPluginSafeUpgrade was created to make your life easier.', MPSU_LOCALE).' ';
			echo __('If you find it useful please make a donation, even a small amount, you will recognize my work and encourage me to go on with the development and support of this and all my other plugins!', MPSU_LOCALE);

			echo '<div align="center" style="margin-top:12px;text-align:center;">'
					.'<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'
						.'<input type="hidden" name="cmd" value="_s-xclick">'
						.'<input type="hidden" name="hosted_button_id" value="8NVPF2WRS4AGC">'
						.'<input type="image" src="https://www.paypal.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">'
						.'<img alt="" border="0" src="https://www.paypal.com/it_IT/i/scr/pixel.gif" width="1" height="1">'
					.'</form>'
				.'</div>'
			;
			echo '<p>'.__('<b><i>Thank you so much!</i></b>', MPSU_LOCALE).'</p>';

			echo '<div style="margin:0;">'
					.'<img style="margin-right:8px;" src="http://myeasywp.com/common/img/camaleo.gif" align="absmiddle" /> '
					.'<a href="http://wordpress.org/extend/plugins/profile/camaleo" target="_blank">'.__('Camaleo\'s plugins page at WordPress.org', MPSU_LOCALE).'</a>'
					.' | '
					.'<a href="http://myeasywp.com" target="_blank">myeasywp.com: '.__('Camaleo\'s plugins official site', MPSU_LOCALE).'</a>'
				.'</div>'
			;
			#-------------
			#	0.0.2: END
			#

			$js = 'document.getElementById(\'processing\').style.display=\'none\';';
		}

		echo $splitter_block
			.$js;

		exit();
		break;
		#
	#---------------------------
	default:
	#---------------------------
		echo '<fieldset style="color:#000000;background:#ffffff;margin:0px;padding:6px;font-family:monospace;font-size:12px;">'
					.'<div align="center">'
						.'<img src="http://myeasywp.com/common/img/warning.gif" border="0" alt="WARNING!" /><br />'
						.'Missing AJAX command...<br />'
		;
		$err = '';
		foreach($_INPUT as $key=>$val)
		{
			$err .= $key.'=>'.$val.', ';
		}
		echo substr($err,0,-2)
			.'</div>'
			.'<br /></fieldset>'
		;
}

?>