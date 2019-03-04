<?php
/**
 * Plugin Name: Flexi Pages Widget
 * Plugin URI: http://srinig.com/wordpress/plugins/flexi-pages/
 * Description: A highly configurable WordPress sidebar widget to list pages and sub-pages. User friendly widget control comes with various options.
 * Version: 1.7.3
 * Author: Srini G
 * Author URI: http://srinig.com/wordpress
 * Text Domain: flexipages
 * Domain Path: /languages/
 * License: GPL2
 */

/*  Copyright 2007-2016 Srini G (email : srinig.com@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once( 'inc/class-flexi-pages.php' );
include_once( 'inc/class-flexi-pages-widget.php' );

define( 'FLEXIPAGES_VERSION', '1.7.2' );

function flexipages_init()
{
	if( $old_widget_options = get_option( 'flexipages_widget') ) {
		if( get_option( 'widget_flexipages') ) {
			update_option( 'widget_flexipages', $old_widget_options );
		} else {
			add_option( 'widget_flexipages', $old_widget_options );
		}
		delete_option( 'flexipages_widget' );
	}

	$plugin_version_stored = get_option( 'flexipages_version' );
	if( $plugin_version_stored != FLEXIPAGES_VERSION ) {
		add_option( 'flexipages_version', FLEXIPAGES_VERSION );
	}

	if(function_exists('load_plugin_textdomain'))
		load_plugin_textdomain('flexipages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );



	/**
	 * The flexipages() template function
	 */
	function flexipages( $args = array() ) {

		$options = array();
		if( is_string( $args ) ) {
			$key_value = explode('&', $args);
			foreach($key_value as $value) {
				$x = explode('=', $value);
				$options[$x[0]] = $x[1]; // $options['key'] = 'value';
			}
		}
		else if( is_array( $args) ) {
			$options = $args;
		}


		$flexipages = new Flexi_Pages( $options );

		if( isset( $options['dropdown'] ) && $options['dropdown'] ) {
			$display = $flexipages->get_dropdown();
		}
		else {
			$display = $flexipages->get_list();
		}

		if( isset( $options['echo'] ) && !$options['echo'] ) {
			return $display;
		}
		else {
			echo $display;
		}

	}

	/** Alias of flexipages() function */
	function flexi_pages( $args = array() ) {
		return flexipages( $args );
	}

}

add_action( 'plugins_loaded', 'flexipages_init' );

add_action( 'widgets_init', array('Flexi_Pages_Widget', 'register') );


/**
 * Add navigation title to pages
 */

/* Create one or more meta boxes to be displayed on the post editor screen. */
function flexipages_add_page_navigation_title() {

  add_meta_box(
    'navigation_title',
    esc_html__( 'Navigation title', 'flexipages' ),
    'flexipages_add_page_navigation_title_callback',
    'page',
    'side',
    'default'
  );
}
add_action( 'add_meta_boxes', 'flexipages_add_page_navigation_title' );

function flexipages_add_page_navigation_title_callback( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'navigation_title_class_nonce' ); ?>

  <p>
    <label for="navigation_title"><?php _e( "Title in page navigation sidebar.", 'flexipages' ); ?></label>
    <br />
    <input class="widefat" type="text" name="navigation_title" id="navigation_title" value="<?php echo esc_attr( get_post_meta( $object->ID, 'navigation_title', true ) ); ?>" size="30" />
  </p>
<?php }

function flexipages_save_page_navigation_title($post_id, $post)
{
    /* Verify the nonce before proceeding. */
    if (!isset($_POST['navigation_title']) || !wp_verify_nonce($_POST['navigation_title_class_nonce'], basename(__FILE__)))
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object($post->post_type);

    /* Check if the current user has permission to edit the post. */
    if (!current_user_can($post_type->cap->edit_post, $post_id))
        return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset($_POST['navigation_title']) ? sanitize_text_field($_POST['navigation_title']) : '' );

    /* Get the meta key. */
    $meta_key = 'navigation_title';

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta($post_id, $meta_key, true);

    /* If a new meta value was added and there was no previous value, add it. */
    if ($new_meta_value && '' == $meta_value)
        add_post_meta($post_id, $meta_key, $new_meta_value, true);

    /* If the new meta value does not match the old value, update it. */
    elseif ($new_meta_value && $new_meta_value != $meta_value){
        update_post_meta($post_id, $meta_key, $new_meta_value);
    }

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ('' == $new_meta_value && $meta_value)
        delete_post_meta($post_id, $meta_key, $meta_value);
}
add_action( 'save_post', 'flexipages_save_page_navigation_title', 10, 2 );

function filter_navigation_title($title ){
    $page = get_page_by_title( $title);
    $navigation_title = $page->navigation_title;
    if(!empty($navigation_title)){
        return $navigation_title;
    }
    return $title;
}

function flexipages_wp_enqueue_scripts() {
    wp_enqueue_style( 'flexipages-style', '/wp-content/plugins/flexi-pages-widget/style.css' );
}
add_action( 'wp_enqueue_scripts', 'flexipages_wp_enqueue_scripts' );

?>
