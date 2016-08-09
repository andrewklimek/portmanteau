<?php
/*
Plugin Name: Portmanteau
Plugin URI:  https://github.com/andrewklimek/portmanteau/
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
add_action( 'add_meta_boxes_project', 'portmanteau_add_custom_box' );

/**
* Shortcode for category listing, used for timeline
*/
add_shortcode( 'quickcat', 'quickcat');
function quickcat($atts){
	$atts = shortcode_atts( array(
		'cat' => '',
		'num' => '16',
		'order' => 'DESC',
		'chars' => 80,
		'type' => 'post'
	), $atts, 'quickcat' );

	$query = new WP_Query( array( 
		'category_name' => $atts['cat'], 
		'posts_per_page' => $atts['num'], 
		'order' => $atts['order'], 
		'post_type' => $atts['type']
	) );	
	
	ob_start();

	// The Loop
	if ( $query->have_posts() ) {
		echo '<div class="quickcat">';
		while ( $query->have_posts() ) {
			$query->the_post();
			// get_template_part( 'template-parts/content', get_post_format() );
			?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark">
	<header class="entry-header">
		<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
	</header>
	<div class="entry-content">
		<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail();
			}
			
			if ( $excerpt = get_the_excerpt() ) {
				if ( strlen( $excerpt ) > (int) $atts['chars'] ) {
					$excerpt = substr( $excerpt, 0, strpos( $excerpt, ' ', (int) $atts['chars'] ) );
				}
				print "<p class='quickcat-excerpt'>{$excerpt}…</p>";
			}
		?>
	</div>
	<span class="readmore">Read More &rarr;</span>
</a>
</article>
			<?php
		}
		echo '</div>';
		the_posts_navigation();
	}
	/* Restore original Post Data */
	wp_reset_postdata();

	return ob_get_clean();
}

/**
* Masonry Shortcode
*/
add_shortcode('frenchmason', 'frenchpress_masonry' );
function frenchpress_masonry( $a, $content = '' ) {
	
	if ( ! $content ) return "no content in [frenchmason] shortcode";
	
	$child = !empty( $a['child'] ) ? ' > :first-child' : '';
	$selector = !empty( $a['selector'] ) ? $a['selector'] : '#frenchmason > *';
	if ( empty( $a['width'] ) ) {
		$width = "'{$selector}'";
	} elseif ( is_numeric( $a['width'] ) ) {
		$width = $a['width'];
	} else {
		$width = "'{$a['width']}'";
	}
	
	$snippet = "
	var grid = document.querySelector('#frenchmason{$child}');
	imagesLoaded( grid, function() {
		var msnry = new Masonry( grid, {
			itemSelector: '{$selector}',
			columnWidth: {$width},
			percentPosition: true,
			// gutter: 10
		});
	});
	";
	wp_enqueue_script( 'masonry' );
	wp_add_inline_script( 'masonry', $snippet );
	
	$out = "<div id='frenchmason' class='frenchmason'>". do_shortcode($content) ."</div>";
	
	return $out;
}

/***
 * Silly shortcode for displaying custom meta
 * for example:
 * <ul>
 * <li><a href="[postmeta key=website]">Website</a></li>
 * <li><a href="[postmeta key=facebook]">Facebook</a></li>
 * <li><a href="[postmeta key=twitter]">Twitter</a></li>
 * <li><a href="[postmeta key=instagram]">Instagram</a></li>
 * </ul>
 */
add_shortcode( 'postmeta', 'readycat_postmeta' );
function readycat_postmeta( $a ) {
	
	$meta = wp_cache_get( 'readycat_postmeta' );// get meta from cache
	
	if ( false === $meta ) {// no cache?
		$meta = get_post_meta( get_the_ID() );// get meta array
		wp_cache_set( 'readycat_postmeta', $meta );// set cache
	}
	
	$return = isset( $meta[$a['key']] ) ? implode( ', ', $meta[$a['key']] ) : '';
	
	return $return;
	
}


function portmanteau_add_custom_box() {

	add_meta_box(
		'portmanteau-id',            // Unique ID
		'Project Info',      // Box title
		'portmanteau_inner_custom_box'  // Content callback
	);
}


/**
 * add the meta box.  As of 4.4, we don't need to do ANYTHIGN to actually write the meta on post save.
 * It is automatically by using name='meta_input[custom_meta_key]'
 * See https://github.com/WordPress/WordPress/blob/e6267dcf19f1309954e04b65a7fa8e9e2df5d0a4/wp-includes/post.php#L2825
 */
function portmanteau_inner_custom_box( $post ) {
	$values = get_post_meta( $post->ID );
	
	$fields = array(
		'Website'	=>	'//hillaryraimo.com/',
		'Facebook'	=>	'https://www.facebook.com/HillaryRaimo/',
		'Twitter'	=>	'https://twitter.com/hillaryraimo/',
		'Instagram'	=>	'https://www.instagram.com/hillaryraimo11/',
	);

	print "<table class='form-table'><tbody>";
	
	foreach ( $fields as $label => $default ) {
		$field = strtolower( str_replace( ' ', '_', $label ) );
		print "
	<tr>
		<th scope='row'><label for='portmanteau_{$field}'>{$label}</label></th>
		<td><input name='meta_input[{$field}]' type='text' id='portmanteau_{$field}' value='";
	print !empty( $values[ $field ][0] ) ? $values[ $field ][0] : $default;
	print "' class='regular-text ltr'></td>
	</tr>";
	}

	print "</tbody></table>";

}


function portmanteau_register_custom_post_types() {

	register_post_type( 'project', array(
		'label'               => 'Projects',
		'public'              => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-portfolio',
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'has_archive'         => true,
		'rewrite'            => array( 'slug' => 'project' ),
	) );

}

function portmanteau_flush_rewrites() {
	portmanteau_register_custom_post_types();
	flush_rewrite_rules();
}
