<?php
/**
 * Flowplayer 5 for WordPress
 *
 * @package   Flowplayer 5 for WordPress
 * @author    Ulrich Pogson <ulrich@pogson.ch>
 * @license   GPL-2.0+
 * @link      https://flowplayer.org/
 * @copyright 2013 Flowplayer Ltd
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Flowplayer Shortcode Class
 *
 * @package Flowplayer5_Shortcode
 * @author  Ulrich Pogson <ulrich@pogson.ch>
 *
 * @since 1.3.0
 */
class Flowplayer5_Shortcode {

	/**
	 * Add shortcode
	 *
	 * @since    1.3.0
	 */
	public function __construct() {
		// Register shortcode.
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register() {
		add_shortcode( 'flowplayer', 'fp5_video_output' );
	}

	public function flowplayer_shortcode_atts() {
		if ( is_404() || isset( $this->shortcode_atts ) ) {
			return;
		}

		$post           = get_queried_object();
		$shortcode_atts = array();

		if ( 'flowplayer5' == get_post_type() && is_single() ) {
			$shortcode_atts['id'] = $post->ID;
		} elseif ( isset( $post->post_content ) ) {
			$shortcode_atts = fp5_has_shortcode_arg( $post->post_content, 'flowplayer' );
		} else {
			global $wp_query;
			foreach ( $wp_query->posts as $post ) {
				if ( isset( $post->post_content ) ) {
					$shortcode_atts = fp5_has_shortcode_arg( $post->post_content, 'flowplayer' );
					if ( ! $shortcode_atts ) {
						continue;
					}
				}
			}
		}
		$this->shortcode_atts = array_filter( $shortcode_atts );
		return $this->shortcode_atts;
	}

	public function has_flowplayer_shortcode() {
		return ! empty( $this->flowplayer_shortcode_atts() );
	}

	public function has_flowplayer_video() {
		if ( isset( $this->has_flowplayer_video ) ){
			return;
		}

		$has_video = 'flowplayer5' == get_post_type() || is_active_widget( false, false, 'flowplayer5-video-widget', true );
		$has_video = apply_filters( 'fp5_filter_has_shortcode', $has_video );

		if ( ! $has_video ) {
			$has_video = $this->has_flowplayer_shortcode();
		}

		$this->has_flowplayer_video = $has_video;
	}

	public function get_video_qualities( $atts ) {
		return apply_filters( 'fp5_filter_video_qualities', $this->get_attr_value( 'qualities', $atts ) );
	}

	public function get_attr_value( $key, $atts ) {
		if ( empty( $atts ) ) {
			return false;
		}
		$video_meta_values = array();
		foreach ( $atts as $id_key => $value ) {
			if ( empty( $value[ $key ] ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$video_meta_values[ $id_key ] = isset( $value[ $key ] ) ? $value[ $key ] : '';
			} else {
				$video_meta_values[ $id_key ] = isset( $atts[ $id_key ][ $key ] ) ? $atts[ $id_key ][ $key ] : '';
			}
		}
		return array_filter( $video_meta_values );
	}

}
