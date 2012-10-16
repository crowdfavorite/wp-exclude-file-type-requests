<?php
/*
Plugin Name: Exclude File Type Requests 
Plugin URI: http://crowdfavorite.com/wordpress/plugins/exclude-file-type-requests 
Description: Don't pass requests for certain file types to WordPress for 404 handling. 
Version: 1.0 
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

load_plugin_textdomain('exclude-file-type-requests');

function cfeftr_request_handler() {
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfeftr_update_settings':
				if (!check_admin_referer('cfeftr_update_settings')) {
					wp_die(__('Error, invalid security check.', 'exclude-file-type-requests'));
				}
				cfeftr_update_settings();
				wp_redirect(admin_url('options-general.php?page='.basename(__FILE__).'&updated=true'));
				die();
				break;
		}
	}
}
add_action('init', 'cfeftr_request_handler');

function cfeftr_resources() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfeftr_admin_css':
				cfeftr_admin_css();
				die();
				break;
		}
	}
}
add_action('init', 'cfeftr_resources', 1);

function cfeftr_admin_css() {
	header('Content-type: text/css');
?>
fieldset.options div.option {
	background: #EAF3FA;
	margin-bottom: 8px;
	padding: 10px;
}
fieldset.options div.option label {
	display: block;
	float: left;
	font-weight: bold;
	margin-right: 10px;
	width: 150px;
}
fieldset.options div.option span.help {
	color: #666;
	display: block;
	font-size: 11px;
	margin-left: 8px;
}
#cfeftr_file_types {
	height: 200px;
	width: 100px;
}
<?php
	die();
}

function cfeftr_admin_head() {
	echo '<link rel="stylesheet" type="text/css" href="'.admin_url('?cf_action=cfeftr_admin_css" />');
}
if (isset($_GET['page']) && $_GET['page'] == basename(__FILE__)) {
	add_action('admin_head', 'cfeftr_admin_head');
}

$cfeftr_settings = array(
	'cfeftr_file_types' => array(
		'type' => 'textarea',
		'label' => 'File Types',
		'default' => 'gif'."\n".'jpg'."\n".'png'."\n".'pdf'."\n".'mp3'."\n".'avi'."\n".'mpeg'."\n".'bmp'."\n".'mov',
		'help' => '',
	),
);

function cfeftr_setting($option) {
	$value = get_option($option);
	if (empty($value)) {
		global $cfeftr_settings;
		$value = $cfeftr_settings[$option]['default'];
	}
	return $value;
}

function cfeftr_admin_menu() {
	if (current_user_can('manage_options')) {
		add_options_page(
			__('Exclude File Type Requests Settings', 'exclude-file-type-requests')
			, __('Exclude File Types', 'exclude-file-type-requests')
			, 10
			, basename(__FILE__)
			, 'cfeftr_settings_form'
		);
	}
}
add_action('admin_menu', 'cfeftr_admin_menu');

function cfeftr_plugin_action_links($links, $file) {
	$plugin_file = basename(__FILE__);
	if (basename($file) == $plugin_file) {
		$settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', 'exclude-file-type-requests').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'cfeftr_plugin_action_links', 10, 2);

if (!function_exists('cf_settings_field')) {
	function cf_settings_field($key, $config) {
		$option = get_option($key);
		if (empty($option) && !empty($config['default'])) {
			$option = $config['default'];
		}
		$label = '<label for="'.$key.'">'.$config['label'].'</label>';
		$help = '<span class="help">'.$config['help'].'</span>';
		switch ($config['type']) {
			case 'select':
				$output = $label.'<select name="'.$key.'" id="'.$key.'">';
				foreach ($config['options'] as $val => $display) {
					$option == $val ? $sel = ' selected="selected"' : $sel = '';
					$output .= '<option value="'.$val.'"'.$sel.'>'.htmlspecialchars($display).'</option>';
				}
				$output .= '</select>'.$help;
				break;
			case 'textarea':
				$output = $label.'<textarea name="'.$key.'" id="'.$key.'">'.htmlspecialchars($option).'</textarea>'.$help;
				break;
			case 'string':
			case 'int':
			default:
				$output = $label.'<input name="'.$key.'" id="'.$key.'" value="'.htmlspecialchars($option).'" />'.$help;
				break;
		}
		return '<div class="option">'.$output.'<div class="clear"></div></div>';
	}
}

function cfeftr_settings_form() {
	global $cfeftr_settings;

	print('
 <div class="wrap">
 	<h2>'.__('Exclude File Type Requests Settings', 'exclude-file-type-requests').'</h2>
 	<form id="cfeftr_settings_form" name="cfeftr_settings_form" action="'.admin_url('options-general.php').'" method="post">
 		<p>'.__('Enter each file extension you want to exclude on a new line, do not include the period (.) before the file extension. Example: gif, not .gif', 'exclude-file-type-requests').'</p>
 		<input type="hidden" name="cf_action" value="cfeftr_update_settings" />
 		<fieldset class="options">
 	');
	foreach ($cfeftr_settings as $key => $config) {
		echo cf_settings_field($key, $config);
	}
	print('
		</fieldset>
		<p>'.sprintf(__('Once you have updated your file exclusion settings here, you need to <a href="%s">update your permalinks</a>.', 'exclude-file-type-requests'), admin_url('options-permalink.php')).'</p>
		<p class="submit">
			<input type="submit" name="submit" value="'.__('Save Settings', 'exclude-file-type-requests').'" class="button-primary" />
		</p>
	');
 	wp_nonce_field('cfeftr_update_settings');
	print('
	</form>
</div>
	');
}

function cfeftr_update_settings() {
	if (!current_user_can('manage_options')) {
		return;
	}
	global $cfeftr_settings;
	foreach ($cfeftr_settings as $key => $option) {
		$value = '';
		switch ($option['type']) {
			case 'int':
				$value = intval($_POST[$key]);
				break;
			case 'select':
				$test = stripslashes($_POST[$key]);
				if (isset($option['options'][$test])) {
					$value = $test;
				}
				break;
			case 'string':
			case 'textarea':
			default:
				$value = stripslashes($_POST[$key]);
				break;
		}
		update_option($key, $value);
	}
}

function cfeftr_mod_rewrite_rules($rules) {
	$file_types = cfeftr_setting('cfeftr_file_types');
	$file_types = str_replace(
		array("\n", "\r", PHP_EOL),
		"\n",
		$file_types
	);
	$types = explode("\n", $file_types);
	if (count($types)) {
		$checked = array();
		foreach ($types as $type) {
			if (!empty($type)) {
				$checked[] = $type;
			}
		}
		if (count($checked)) {
			$exclude = 'RewriteCond %{REQUEST_URI} !.*.(?:'.implode('|', $checked).'$)'."\n";
			$rules = str_replace('RewriteCond %{REQUEST_FILENAME} !-d', 'RewriteCond %{REQUEST_FILENAME} !-d'."\n".$exclude, $rules);
		}
	}
	return $rules;
}
add_action('mod_rewrite_rules', 'cfeftr_mod_rewrite_rules');

//a:23:{s:11:"plugin_name";s:26:"Exclude File Type Requests";s:10:"plugin_uri";s:69:"http://crowdfavorite.com/wordpress/plugins/exclude-file-type-requests";s:18:"plugin_description";s:73:"Don't pass requests for certain file types to WordPress for 404 handling.";s:14:"plugin_version";s:3:"1.0";s:6:"prefix";s:6:"cfeftr";s:12:"localization";s:26:"exclude-file-type-requests";s:14:"settings_title";s:35:"Exclude File Type Requests Settings";s:13:"settings_link";s:18:"Exclude File Types";s:4:"init";b:0;s:7:"install";b:0;s:9:"post_edit";b:0;s:12:"comment_edit";b:0;s:6:"jquery";b:0;s:6:"wp_css";b:0;s:5:"wp_js";b:0;s:9:"admin_css";s:1:"1";s:8:"admin_js";b:0;s:8:"meta_box";b:0;s:15:"request_handler";s:1:"1";s:6:"snoopy";b:0;s:11:"setting_cat";b:0;s:14:"setting_author";b:0;s:11:"custom_urls";s:1:"1";}

?>