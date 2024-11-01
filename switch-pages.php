<?php
/**
 * @package switch-pages
 * @version 2.0
 */
/*
Plugin Name: Switch Pages
Plugin URI: http://wordpress.org/extend/plugins/switch-pages/
Description: Switch Pages is a plugin which allows you to switch between pages/posts from within the Edit page without having to go to the Pages tab and searching for the page/post you want to edit. You will get a dropdown list of pages/posts from which you can select which page you would like to edit.
Version: 2.0
Author: Brijesh Kothari
License: GPLv3 or later
*/

/*
Copyright (C) 2013  Brijesh Kothari (email : admin@wpinspired.com)
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

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

define('switch_pages_version', '1.0.1');

// Ok so we are now ready to go
register_activation_hook( __FILE__, 'switch_pages_activation');

function switch_pages_activation(){

add_option('switch_pages_version', switch_pages_version);

}

add_action( 'plugins_loaded', 'switch_pages_update_check' );

function switch_pages_update_check(){

global $wpdb;

	$sql = array();
	$current_version = get_option('switch_pages_version');

	/* if(version_compare($current_version, 1.0.0, '<')){
		
	} */

	if(version_compare($current_version, switch_pages_version, '<')){
		
		if(!empty($sql)){
			foreach($sql as $sk => $sv){
				$wpdb->query($sv);
			}
		}

		update_option('switch_pages_version', switch_pages_version);
	}

}

// Add settings link on plugin page
/* function switch_pages_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=switch-pages">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
} 
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'switch_pages_settings_link' );*/
	
// Show the switch dropdown
// Supported for Classic Editor
add_action('edit_form_top', 'switch_pages_switcher');

function switch_pages_switcher(){
	
	global $post;
	
	$args = array(
		'numberposts' => -1,
		'post_type' => $post->post_type,
		'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash')
	);
	
	$all_posts = get_posts($args);
	
	if(!empty($all_posts)){
		echo '
		<script>
		function jump_to_fn(post_id){
			var wp_admin_url = "'.admin_url().'";
			var this_post = "'.$post->ID.'";
			if(post_id && post_id != this_post){
				window.location = wp_admin_url+"post.php?post="+post_id+"&action=edit";
				return true;
			}
		}
		</script>
		
		<br /><font size="+0.5">Jump to another '.$post->post_type.' : </font>
		<select name="jump_to" id="jump_to" onchange="jump_to_fn(this.value)">';
		
		foreach($all_posts as $k => $pv){
			echo '<option value="'.$pv->ID.'" '.($pv->ID == $post->ID ? 'selected="selected"' : '').'>'.$pv->post_title.'</option>';
		}
		
		echo '</select>';
	}
}

// Supported for Gutenberg
function switch_pages_plugin_register() {
	
	register_meta( 'post', 'switch_pages_title', array(
 		'type'		=> 'string',
 		'single'	=> true,
 		'show_in_rest'	=> true,
 	) );
	
	
    wp_register_script(
        'switch-pages-sidebar-js',
        plugins_url( 'js/sidebar.js', __FILE__ ),
        array(
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components'
        )
    );
	
    wp_register_style(
        'switch-pages-sidebar-css',
        plugins_url( 'switch-pages-sidebar.css', __FILE__ )
    );
}
add_action( 'init', 'switch_pages_plugin_register' );
 
function switch_pages_script_enqueue() {
    wp_enqueue_script( 'switch-pages-sidebar-js' );
}
add_action( 'enqueue_block_editor_assets', 'switch_pages_script_enqueue' );
 
/* function switch_pages_style_enqueue() {
    wp_enqueue_style( 'switch-pages-sidebar-css' );
}
add_action( 'enqueue_block_assets', 'switch_pages_style_enqueue' ); */

// Sorry to see you going
register_uninstall_hook( __FILE__, 'switch_pages_deactivation');

function switch_pages_deactivation(){

delete_option('switch_pages_version');

}