<?php
/*
Plugin Name: Portmanteau
Description: Typical portfolio / project plugin
Version:     0.1
Author:      Andrew J Klimek
Author URI:  https://readycat.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Portmanteau is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.

Portmanteau is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
Portmanteau. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if(!function_exists('poo')){function poo($v,$l=''){if(WP_DEBUG_LOG){error_log("***$l***\n".var_export($v,true));}}}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'portmanteau_flush_rewrites' );
add_action( 'init', 'portmanteau_register_custom_post_types' );
add_action( 'add_meta_boxes_portmanteau', 'portmanteau_add_custom_box' );


function portmanteau_add_custom_box() {

	add_meta_box(
		'portmanteau-id',            // Unique ID
		'Project Info',      // Box title
		'portmanteau_inner_custom_box'  // Content callback
	);
}


/**
 * add the meta box.  As of 4.4, we don't need to do ANYTHIGN to actually add the meta on post save.
 * It is automatically by using name='meta_input[custom_meta_key]'
 * See https://github.com/WordPress/WordPress/blob/e6267dcf19f1309954e04b65a7fa8e9e2df5d0a4/wp-includes/post.php#L2825
 */
function portmanteau_inner_custom_box( $post ) {
	$values = get_post_meta( $post->ID );
	
	$fields = array(
		'website'	=>	'Website',
		'facebook'	=>	'Facebook',
		'twitter'	=>	'Twitter',
		'instagram'	=>	'Instagram',
	);

	print "<table class='form-table'><tbody>";
	
	foreach ( $fields as $field => $label ) {
		print "
	<tr>
		<th scope='row'><label for='portmanteau_{$field}'>{$label}</label></th>
		<td><input name='meta_input[{$field}]' type='text' id='portmanteau_{$field}' value='{$values[ $field ][0]}' class='regular-text ltr'></td>
	</tr>";
	}

	print "</tbody></table>";

}


function portmanteau_register_custom_post_types() {

	register_post_type( 'portmanteau', array(
		'label'               => 'Projects',
		'public'              => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-portfolio',
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'has_archive'         => true,
		'rewrite'            => array( 'slug' => 'projects' ),
	) );

}

function portmanteau_flush_rewrites() {
	portmanteau_register_custom_post_types();
	flush_rewrite_rules();
}