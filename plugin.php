<?php
/**
 * Plugin Name: Fresco Lightbox
 * Plugin URI: http://github.com/chrismccoy/fresco
 * Description: Use this plugin to implement the fresco lightbox
 * Version: 2.0
 * Author: Chris McCoy
 * Author URI: http://github.com/chrismccoy

 * @copyright 2020
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
			add_filter( 'media_send_to_editor', array( $this, 'media_filter'), 20, 3);
			add_action( 'init', array( $this, 'embeds' ));
			add_filter( 'oembed_dataparse', array($this, 'oembed_services'), 10, 3);
			add_action( 'admin_menu', array( $this, 'fresco_add_admin_menu' ));
			add_action( 'admin_init', array( $this, 'fresco_settings_init' ));
		}

		/**
		 * add options page 
		 *
		 * @since 1.0
		 */

		function fresco_add_admin_menu() { 
			add_options_page( 'Fresco Lightbox', 'Fresco Lightbox', 'manage_options', 'fresco_lightbox', array($this, 'fresco_lightbox_options_page' ));
		}

		/**
		 * adding settings section and fields
		 *
		 * @since 1.0
		 */

		function fresco_settings_init() { 

			register_setting( 'fresco_plugin_page', 'fresco_settings' );

			if( false == get_option( 'fresco_settings' ) ) { 

				$defaults = array(
					'fresco_ui_single' => 'outside',
					'fresco_ui_group' => 'outside',
					'fresco_ui_video' => 'outside',
					'fresco_group_thumbnails' => 'horizontal',
				);

				add_option( 'fresco_settings', $defaults );
			}

			add_settings_section(
				'fresco_plugin_page_section', 
				null,
				null,
				'fresco_plugin_page'
			);

			add_settings_field( 
				'fresco_ui_single', 
				__( 'Single Image Close Button', 'fresco' ), 
				array($this, 'fresco_ui_single_render'), 
				'fresco_plugin_page', 
				'fresco_plugin_page_section' 
			);

			add_settings_field( 
				'fresco_ui_group', 
				__( 'Gallery Image Close Button', 'fresco' ), 
				array($this, 'fresco_ui_group_render'), 
				'fresco_plugin_page', 
				'fresco_plugin_page_section' 
			);

			add_settings_field( 
				'fresco_ui_video', 
				__( 'Video Close Button', 'fresco' ), 
				array($this, 'fresco_ui_video_render'), 
				'fresco_plugin_page', 
				'fresco_plugin_page_section' 
			);

			add_settings_field( 
				'fresco_group_thumbnails', 
				__( 'Thumbnail Gallery Position', 'fresco' ), 
				array($this, 'fresco_group_thumbnails_render'), 
				'fresco_plugin_page', 
				'fresco_plugin_page_section' 
			);

		}

		/**
		 * render select field for fresco UI Single Images
		 *
		 * @since 1.0
		 */

		function fresco_ui_single_render() { 

			$options = get_option( 'fresco_settings' );
			?>
			<select name='fresco_settings[fresco_ui_single]'>
				<option value='outside' <?php selected( $options['fresco_ui_single'], 'outside' ); ?>>Outside (default)</option>
				<option value='inside' <?php selected( $options['fresco_ui_single'], 'inside' ); ?>>Inside</option>
			</select>
		<?php
		}

		/**
		 * render select field for fresco UI group settings
		 *
		 * @since 1.0
		 */

		function fresco_ui_group_render() { 

			$options = get_option( 'fresco_settings' );
			?>
			<select name='fresco_settings[fresco_ui_group]'>
				<option value='outside' <?php selected( $options['fresco_ui_group'], 'outside' ); ?>>Outside (default)</option>
				<option value='inside' <?php selected( $options['fresco_ui_group'], 'inside' ); ?>>Inside</option>
			</select>
		<?php
		}

		/**
		 * render select field for fresco UI video settings
		 *
		 * @since 1.0
		 */

		function fresco_ui_video_render() { 

			$options = get_option( 'fresco_settings' );
			?>
			<select name='fresco_settings[fresco_ui_video]'>
				<option value='outside' <?php selected( $options['fresco_ui_video'], 'outside' ); ?>>Outside (default)</option>
				<option value='inside' <?php selected( $options['fresco_ui_video'], 'inside' ); ?>>Inside</option>
			</select>
		<?php
		}

		/**
		 * render select field for fresco UI group thumbnail settings
		 *
		 * @since 1.0
		 */

		function fresco_group_thumbnails_render() { 

			$options = get_option( 'fresco_settings' );
			?>
			<select name='fresco_settings[fresco_group_thumbnails]'>
				<option value='horizontal' <?php selected( $options['fresco_group_thumbnails'], 'horizontal' ); ?>>Horizontal (default)</option>
				<option value='vertical' <?php selected( $options['fresco_group_thumbnails'], 'vertical' ); ?>>Vertical</option>
				<option value='false' <?php selected( $options['fresco_group_thumbnails'], 'false' ); ?>>Off</option>
			</select>
		<?php
		}

		/**
		 * settings options page form
		 *
		 * @since 1.0
		 */

		function fresco_lightbox_options_page() { 

			?>
			<form action='options.php' method='post'>

				<h2>Fresco Lightbox</h2>
				<b>Options for the Fresco Lightbox WordPress Plugin</b>
				<?php
				settings_fields( 'fresco_plugin_page' );
				do_settings_sections( 'fresco_plugin_page' );
				submit_button();
				?>
			</form>
			<?php
		}

		/**
		 * enqueue fresco lightbox javascript
		 *
		 * @since 1.0
		 */

		function scripts() {
			wp_enqueue_script( 'fresco_js', plugins_url( 'js/fresco.min.js', __FILE__ ), array( 'jquery' ), '1.0', false );

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

			if ( @file_exists( get_stylesheet_directory() . '/fresco_custom.css' ) )
				$css_file = get_stylesheet_directory_uri() . '/fresco_custom.css';
			elseif ( @file_exists( get_template_directory() . '/fresco_custom.css' ) )
				$css_file = get_template_directory_uri() . '/fresco_custom.css';
			else
				$css_file = plugins_url( 'css/custom.css', __FILE__ );

			wp_enqueue_style( 'fresco_custom_css', $css_file, false, '1.0', 'screen' );

		}

        	/**
         	* add fresco data attributes to images inserted into post
         	*
         	* @since 1.0
         	*/

		function media_filter($html, $attachment_id) {

			$position_option = get_option( 'fresco_settings' );
			$position = "'" . $position_option['fresco_ui_single'] . "'";

    			$attachment = get_post($attachment_id);

			$types = array('image/jpeg', 'image/gif', 'image/png');

			if(in_array($attachment->post_mime_type, $types) ) {
				$srcset = (wp_get_attachment_image_srcset( $attachment_id, 'thumbnail')) ? 'srcset="' . wp_get_attachment_image_srcset( $attachment_id, 'thumbnail') . '" ' : '';
				$sizes = (wp_get_attachment_image_sizes( $attachment_id, 'thumbnail')) ? 'sizes="' . wp_get_attachment_image_sizes( $attachment_id, 'thumbnail') . '"' : '';
				$fresco_attr = sprintf('class="fresco thumbnail" data-fresco-group="gallery-%s" data-fresco-options="ui: %s"', $attachment->post_parent, $position);
    				$html = '<a href="'. wp_get_attachment_url($attachment_id) .'" '. $fresco_attr .'><img src="'. wp_get_attachment_thumb_url($attachment_id) .'" '.  $srcset . $sizes .'></a>';
			}

			return $html;
		}

		/**
		 * register oembed for image urls for the lightbox
		 *
		 * @since 1.0
		 */

		function embeds() { 
			wp_embed_register_handler( 'detect_lightbox', '#^https?://.+\.(jpe?g|gif|png)$#i', array( $this, 'wp_embed_register_handler') , 10, 3);
		}


        	/**
         	* convert image urls to oembed with fresco markup
         	*
         	* @since 1.0
         	*/

		function wp_embed_register_handler( $matches, $attr, $url, $rawattr ) {
			global $post;

			$position_option = get_option( 'fresco_settings' );
			$position = "'" . $position_option['fresco_ui_single'] . "'";
      			$embed = sprintf('<a href="%s" class="fresco thumbnail" data-fresco-group="gallery-%s" data-fresco-options="ui: %s"><img src="%s" width="%d" height="%d" /></a>', $url, $post->ID, $position, $url, get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
			$embed = apply_filters( 'oembed_detect_lightbox', $embed, $matches, $attr, $url, $rawattr );

    			return apply_filters( 'oembed_result', $embed, $url);
		}

        	/**
         	* filter instagram, flickr, cloudup, imgur, youtube, and vimeo for lightbox
         	*
         	* @since 1.0
         	*/

		function oembed_services($html, $data, $url) {

			$position_option = get_option( 'fresco_settings' );
			$position = "'" . $position_option['fresco_ui_single'] . "'";

        		if ($data->provider_name == 'Instagram') {
                		$html = sprintf('<a href="%s" class="fresco thumbnail" data-fresco-options="ui: %s"><img src="%s" width="%d" height="%d" /></a>', esc_url($data->thumbnail_url), $position, esc_url($data->thumbnail_url), get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
			}

        		if ($data->provider_name == 'Flickr') {
				$url = str_replace('_z.jpg','_b.jpg', $data->url);
                		$html = sprintf('<a href="%s" class="fresco thumbnail" data-fresco-options="ui: %s"><img src="%s" width="%d" height="%d" /></a>', esc_url($url), $position, esc_url($url), get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
			}

        		if ($data->provider_name == 'Cloudup') {
                		$html = sprintf('<a href="%s" class="fresco thumbnail" data-fresco-options="ui: %s"><img src="%s" width="%d" height="%d" /></a>', esc_url($data->url), $position, esc_url($data->url),  get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
			}

      			if ($data->provider_name == 'Imgur') {
                		$html = sprintf('<a href="%s" class="fresco thumbnail" data-fresco-options="ui: %s"><img src="%s" width="%d" height="%d" /></a>', esc_url($url), $position, esc_url($url), get_option('thumbnail_size_w'), get_option('thumbnail_size_h'));
			}

      			if ($data->provider_name == 'YouTube' || $data->provider_name == 'Vimeo') {
                        	$screenshot = wp_get_attachment_url( get_post_thumbnail_id($post_ID) ) ? wp_get_attachment_url( get_post_thumbnail_id($post_ID) ) : 'http://fakeimg.pl/439x230/282828/eae0d0/?text=Click%20to%20Play!';
                		$html = sprintf('<a href="%s" class="fresco" data-fresco-options="ui: %s"><img src="%s" /></a>', esc_url($url), $position, $screenshot);
			}

			return $html;
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

				$position_option = get_option( 'fresco_settings' );
				$position = "'" . $position_option['fresco_ui_group'] . "'";
				$thumbnails = "'" . $position_option['fresco_group_thumbnails'] . "'";
				$thumbnail_show = ($thumbnails == "'false'") ? 'false' : $thumbnails;

    				$output = "\n" . '<div class="fresco_gallery">' . "\n";

    				foreach ( $attachments as $id => $attachment ) {
					$srcset = (wp_get_attachment_image_srcset( $id, 'thumbnail')) ? 'srcset="' . wp_get_attachment_image_srcset( $id, 'thumbnail') . '" ' : '';
					$sizes = (wp_get_attachment_image_sizes( $id, 'thumbnail')) ? 'sizes="' . wp_get_attachment_image_sizes( $id, 'thumbnail') . '"' : '';
					$caption = ($attachment->post_excerpt) ? $attachment->post_excerpt : $post->post_title;
					$fresco_attr = sprintf('class="fresco thumbnail" data-fresco-group="gallery-%s" data-fresco-caption="%s" data-fresco-group-options="ui: %s, thumbnails: %s"', $post->ID, $caption, $position, $thumbnail_show);
	       				$output .= '<a href="'. wp_get_attachment_url($id) .'" '. $fresco_attr. '><img src="'. wp_get_attachment_thumb_url($id) .'" class="fresco thumbnail" '. $srcset . $sizes .'></a>' . "\n";
    				}

    				$output .= "</div>" . "\n";

    			return $output;
		}
   	}
}
