<?php
/*
Plugin Name: Gravity Forms Remaining Entries Shortcode 
Plugin URI: http://ctlt.ubc.ca
Description: Adds an additional shortcode for gravityforms that returns a count of entries remaining.  
Author: CTLT Dev
Version: 0.1

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/

/**
 * This section is the "meat" of the plugin
 * 
 * Thanks to http://gravitywiz.com/2012/09/19/shortcode-display-number-of-entries-left/
 * 
 */
add_filter( 'gform_shortcode_entries_left', 'gwiz_entries_left_shortcode', 10, 2 );
function gwiz_entries_left_shortcode( $output, $atts ) {

    extract( shortcode_atts( array(
        'id' => false,
        'format' => false // should be 'comma', 'decimal'
    ), $atts ) );
 
    if( ! $id )
        return '';
    
    $form = RGFormsModel::get_form_meta( $id );
    if( ! rgar( $form, 'limitEntries' ) || ! rgar( $form, 'limitEntriesCount' ) )
        return '';
        
    $entry_count = RGFormsModel::get_lead_count( $form['id'], '', null, null, null, null, 'active' );
    $entries_left = rgar( $form, 'limitEntriesCount' ) - $entry_count;
    $output = $entries_left;
    
    if( $format ) {
        $format = $format == 'decimal' ? '.' : ',';
        $output = number_format( $entries_left, 0, false, $format );
    }
    
    return $entries_left > 0 ? $output : 0;
}

register_activation_hook(__FILE__, 'gfrs_dependency_check_hook');	//checks if gravityform is installed, does not activate if not.
add_action('admin_init', 'gfrs_dependency_check_hook');	//runs each time plugin is run so that if gravitforms is uninstalled, it also turns off (with error warning)

/**
 * Checks for GravityForms
 * 
 * checks if Gravityforms is activated.  If it is, then either die or deactivate (depending on current filter).
 */
function gfrs_dependency_check_hook() {
	$current_filter = current_filter();
	$test = new Theme_Plugin_Dependency( 'gravityforms', 'http://www.gravityforms.com' );
	if ( !$test->check_active() ) {
		$current_filter = current_filter();
		if ($current_filter == 'activate_gravityforms-remaining-entries-shortcode.php') {
 			wp_die('Sorry, cannot activate plugin without first activating Gravityforms.');
		} else {
			add_action('admin_notices', 'gfres_auto_deactivate_error');
			deactivate_plugins(plugin_basename(__FILE__));
		}
	}
}

/**
 * simple admin_notices callback error message output when auto deactivating the plugin due to absence of GravityForms.
 * @see gfrs_dependency_check_hook()
 */
function gfres_auto_deactivate_error() {
	echo '<div class="error">Auto Deactivating Gravity Forms Remaining Entries Shortcode because GravityForm plugin is not activated.</div>';
}

/*
 Thanks to http://ottopress.com/2012/themeplugin-dependencies/
 
Simple class to let themes add dependencies on plugins in ways they might find useful

Example usage:

$test = new Theme_Plugin_Dependency( 'simple-facebook-connect', 'http://ottopress.com/wordpress-plugins/simple-facebook-connect/' );
if ( $test->check_active() )
	echo 'SFC is installed and activated!';
else if ( $test->check() )
	echo 'SFC is installed, but not activated. <a href="'.$test->activate_link().'">Click here to activate the plugin.</a>';
else if ( $install_link = $test->install_link() )
	echo 'SFC is not installed. <a href="'.$install_link.'">Click here to install the plugin.</a>';
else
	echo 'SFC is not installed and could not be found in the Plugin Directory. Please install this plugin manually.';

*/
if (!class_exists('Theme_Plugin_Dependency')) {
	class Theme_Plugin_Dependency {
		// input information from the theme
		var $slug;
		var $uri;

		// installed plugins and uris of them
		private $plugins; // holds the list of plugins and their info
		private $uris; // holds just the URIs for quick and easy searching

		// both slug and PluginURI are required for checking things
		function __construct( $slug, $uri ) {
			$this->slug = $slug;
			$this->uri = $uri;
			if ( empty( $this->plugins ) )
				$this->plugins = get_plugins();
			if ( empty( $this->uris ) )
				$this->uris = wp_list_pluck($this->plugins, 'PluginURI');
		}

		// return true if installed, false if not
		function check() {
			return in_array($this->uri, $this->uris);
		}

		// return true if installed and activated, false if not
		function check_active() {
			$plugin_file = $this->get_plugin_file();
			if ($plugin_file) return is_plugin_active($plugin_file);
			return false;
		}

		// gives a link to activate the plugin
		function activate_link() {
			$plugin_file = $this->get_plugin_file();
			if ($plugin_file) return wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin='.$plugin_file), 'activate-plugin_'.$plugin_file);
			return false;
		}

		// return a nonced installation link for the plugin. checks wordpress.org to make sure it's there first.
		function install_link() {
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$info = plugins_api('plugin_information', array('slug' => $this->slug ));

			if ( is_wp_error( $info ) )
				return false; // plugin not available from wordpress.org

			return wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $this->slug), 'install-plugin_' . $this->slug);
		}

		// return array key of plugin if installed, false if not, private because this isn't needed for themes, generally
		private function get_plugin_file() {
			return array_search($this->uri, $this->uris);
		}
	}
}