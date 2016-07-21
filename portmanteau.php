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

add_action( 'init', 'portmanteau_register_custom_post_types' );


function portmanteau_register_custom_post_types() {

	register_post_type( 'portmanteau', array(
		'label'               => 'Projects',
		'public'              => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-portfolio',
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'has_archive'         => true,
	) );

}
