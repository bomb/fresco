<?php
/**
 * Plugin Name: Fresco Lightbox
 * Plugin URI: http://github.com/chrismccoy
 * Description: Use this plugin to implement the fresco lightbox
 * Version: 1.0
 * Author: Chris McCoy
 * Author URI: http://github.com/chrismccoy

 * @copyright 2014
 * @author Chris McCoy
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package Fresco_Lightbox
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Initiate Fresco Lightbox Class on plugins_loaded
 *
 * @since 1.0
 */

if ( !function_exists( 'fresco_lightbox' ) ) {

	function fresco_lightbox() {
		$fresco_lightbox = new Fresco_Lightbox();
	}

	add_action( 'plugins_loaded', 'fresco_lightbox' );
}

/**
 * Fresco Lightbox Class for scripts, styles, and shortcode
 *
 * @since 1.0
 */

if( !class_exists( 'Fresco_Lightbox' ) ) {

	class Fresco_Lightbox {

		/**
 		* Hook into hooks for Register styles, scripts, and shortcode
 		*
 		* @since 1.0
 		*/

		function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
			add_filter( 'post_gallery', array( $this, 'gallery'), 10, 2 );

		}

		/**
		 * enqueue fresco lightbox javascript
		 *
		 * @since 1.0
		 */

		function scripts() {
			wp_enqueue_script( 'fresco_js', plugins_url( 'js/fresco.js', __FILE__ ), array( 'jquery' ), '1.0', false );

            		global $is_IE;

            		if( $is_IE ) {
                		wp_enqueue_script( 'css3_media_queries', 'http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js');

		    	}
        	}

		/**
		 * enqueue fresco lightbox styles
		 *
		 * @since 1.0
		 */

		function styles() {
			wp_enqueue_style( 'fresco_css', plugins_url( 'css/fresco.css', __FILE__ ), false, '1.0', 'screen' );
		}

        	/**
         	* modified gallery output for fresco lightbox
         	*
         	* @since 1.0
         	*/

		function gallery( $content, $attr ) {
    			global $instance, $post;

    			$instance++;

    			if ( isset( $attr['orderby'] ) ) {
        			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
        			if ( ! $attr['orderby'] )
            				unset( $attr['orderby'] );
    			}

    			extract( shortcode_atts( array(
        			'order'      =>  'ASC',
        			'orderby'    =>  'menu_order ID',
        			'id'         =>  $post->ID,
        			'itemtag'    =>  'figure',
        			'icontag'    =>  'div',
        			'captiontag' =>  'figcaption',
        			'columns'    =>   3,
        			'size'       =>   'thumbnail',
        			'include'    =>   '',
        			'exclude'    =>   ''
    			), $attr ) );

    			$id = intval( $id );

    			if ( 'RAND' == $order ) {
        			$orderby = 'none';
    			}

    			if ( $include ) {
        
        			$include = preg_replace( '/[^0-9,]+/', '', $include );
        
        			$_attachments = get_posts( array(
            				'include'        => $include,
            				'post_status'    => 'inherit',
            				'post_type'      => 'attachment',
            				'post_mime_type' => 'image',
            				'order'          => $order,
            				'orderby'        => $orderby
        			) );

        			$attachments = array();
        
        			foreach ( $_attachments as $key => $val ) {
            				$attachments[$val->ID] = $_attachments[$key];
        			}

    				} elseif ( $exclude ) {
        
        				$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
        
        				$attachments = get_children( array(
            					'post_parent'    => $id,
            					'exclude'        => $exclude,
            					'post_status'    => 'inherit',
            					'post_type'      => 'attachment',
            					'post_mime_type' => 'image',
            					'order'          => $order,
            					'orderby'        => $orderby
        				) );

    				} else {

        				$attachments = get_children( array(
            					'post_parent'    => $id,
            					'post_status'    => 'inherit',
            					'post_type'      => 'attachment',
            					'post_mime_type' => 'image',
            					'order'          => $order,
            					'orderby'        => $orderby
        				) );

    				}

    				if ( empty( $attachments ) ) {
        				return;
    				}

    				if ( is_feed() ) {
        				$output = "\n";
        				foreach ( $attachments as $att_id => $attachment )
            					$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
        				return $output;
    				}

    				$output = "\n<div class=\"fresco_gallery\">\n";

    				foreach ( $attachments as $id => $attachment ) {
               				$full_image = wp_get_attachment_url($id);
          				$thumbnail = wp_get_attachment_image_src($id, 'thumbnail');
        				$output .= "\t<a href=\"$full_image\" class=\"fresco\" data-fresco-group=\"gallery-{$post->ID}\"><img src=\"$thumbnail[0]\" class=\"fresco_thumbnail\"></a>\n\t";
    				}

    				$output .= "</div>\n";

    			return $output;
		}
   	}
}
