<?php
/*
Plugin Name: Minimalist Custom CSS
Description: adds a meta box to your posts and pages for custom CSS.  CSS is minified before printing to page.
Version:     0.1
Plugin URI:  https://github.com/andrewklimek/mnml-custom-css
Author:      Andrew J Klimek
Author URI:  https://github.com/andrewklimek
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Minimalist Custom CSS is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.
Minimalist Custom CSS is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with 
Minimalist Custom CSS. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

function mnml_custom_css_metabox( $post_type )
{
	// if ( in_array( $post_type, ['attachment','frm_logs','popup','knowledgebase','docs'] ) ) return;

	// if ( ! in_array( $post_type, ['page','post','frm_display'] ) ) return;

	add_meta_box(
		'mnml_custom_css',				// Unique ID
		'Custom CSS',					// Box title
		'mnml_custom_css_metabox_inner'	// Content callback
	);
}
// add_action( 'add_meta_boxes', 'mnml_custom_css_metabox' );
add_action( 'add_meta_boxes_page', 'mnml_custom_css_metabox' );
add_action( 'add_meta_boxes_post', 'mnml_custom_css_metabox' );
        

function mnml_custom_css_metabox_inner( $post )
{

	wp_nonce_field( 'mnml_page_css_save', 'mnml_page_css' );

	$value = get_post_meta( $post->ID, 'mnml_css', true );
	
	echo "<textarea name=mnml_css class='large-text code' rows=18 style=font-size:13px>{$value}</textarea>";
}

function mnml_custom_css_metabox_save( $post_id )
{
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	// nonce
	if ( empty( $_POST['mnml_page_css'] ) || ! wp_verify_nonce( $_POST['mnml_page_css'], 'mnml_page_css_save' ) ) return;

	// Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] )
		if ( ! current_user_can( 'edit_page', $post_id ) ) return;
    elseif ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( empty( $_POST['mnml_css'] ) )
		delete_post_meta( $post_id, 'mnml_css' );
	else
		update_post_meta( $post_id, 'mnml_css', $_POST['mnml_css'] );
}
add_action( 'save_post', 'mnml_custom_css_metabox_save' );

function mnml_print_page_level_css()
{	
	global $wp_query;
	if ( empty($wp_query->queried_object_id) )// or get_queried_object_id() with no global needed..
		return;

	$id = $wp_query->queried_object_id;

	$css = get_post_meta( $id, 'mnml_css', true);
	
	if ( $css ) {
		$css = str_replace(// minimize
			["\r","\n","\t",'   ','  ',': ','; ',', ',' {','{ ',' }','} ',';}'],
			[  '',  '',  '',   '', ' ', ':', ';', ',', '{', '{', '}', '}', '}'],
			preg_replace('|/\*[\s\S]*?\*/|','',$css)
		);
		echo "<style>{$css}</style>";
	}
}
add_action('wp_head', 'mnml_print_page_level_css', 102 );

/**
 * TODO optional delete css on uninstall
function mnml_print_page_level_css_cleanup()
{	
	$options = get_option('mnml_custom_css');
	
	// Clean out styles if that option is checked.
	if ( isset( $options['delete_meta_on_uninstall'] ) ) {
		delete_post_meta_by_key('mnml_css');
	}
}
register_uninstall_hook(__FILE__, 'mnml_print_page_level_css_cleanup');
 *
 */
