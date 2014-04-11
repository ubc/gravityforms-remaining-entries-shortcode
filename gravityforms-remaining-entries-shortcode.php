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
add_filter( 'gform_shortcode_entries_left', 'ctlt_gwiz_entries_left_shortcode', 10, 2 );
function ctlt_gwiz_entries_left_shortcode( $output, $atts ) {

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

register_activation_hook(__FILE__, 'ctlt_gfrs_dependency_check_hook');	//checks if gravityform is installed, does not activate if not.
add_action('admin_init', 'ctlt_gfrs_dependency_check_hook');	//runs each time plugin is run so that if gravitforms is uninstalled, it also turns off (with error warning)

/**
 * Checks for GravityForms
 * 
 * checks if Gravityforms is activated.  If it is, then either die or deactivate (depending on current filter).
 */
function ctlt_gfrs_dependency_check_hook() {
	//check if gravity forms is loaded and activated.
	if (!class_exists('GFForms')) {
		$current_filter = current_filter();
		if ($current_filter == 'activate_gravityforms-remaining-entries-shortcode.php') {
			wp_die('Sorry, cannot activate plugin without first activating Gravityforms.');
		} else {
			add_action('admin_notices', 'ctlt_gfres_auto_deactivate_error');
			deactivate_plugins(plugin_basename(__FILE__));
		}
	}
}

/**
 * simple admin_notices callback error message output when auto deactivating the plugin due to absence of GravityForms.
 * @see ctlt_gfrs_dependency_check_hook()
 */
function ctlt_gfres_auto_deactivate_error() {
	echo '<div class="error">Auto Deactivating Gravity Forms Remaining Entries Shortcode because GravityForm plugin is not activated.</div>';
}