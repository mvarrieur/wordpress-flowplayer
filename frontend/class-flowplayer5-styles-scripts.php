<?php
/**
 * Flowplayer 5 for WordPress
 *
 * @package   Flowplayer5_Frontend
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
 * Initial Flowplayer Frontend class
 *
 * @package Flowplayer5_Frontend
 * @author  Ulrich Pogson <ulrich@pogson.ch>
 *
 * @since 1.14.0
 */
class Flowplayer5_Styles_Scripts {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $atts = array() ) {
		$plugin                    = Flowplayer5::get_instance();
		$plugin_version            = $plugin->get_plugin_version();
		$player_version            = $plugin->get_player_version();
		$this->atts = $atts;
		$this->options = $this->get_options( $atts );
		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'set_shortcode_args' ) );
	}

	function set_shortcode_args() {
		if ( empty( $this->atts ) ) {
			$flowplayer_shortcode      = new Flowplayer5_Shortcode();
			$atts = $flowplayer_shortcode->flowplayer_shortcode_atts();
		}
		if ( isset( $atts['playlist'] ) ) {
			$playlist_id = Flowplayer5_Playlist::wp_get_split_term( $atts['playlist'] );
			$atts = Flowplayer5_Playlist::get_videos_by_id( $playlist_id );
		} else {
			$atts[ 'id' . $atts['id'] ] = self::get_shortcode_attr( $atts );
		}
		$this->atts = $atts;
	}

	public function enqueue_styles_script() {
		if ( $this->options['has_shortcode'] ) {
			$this->register_styles( $this->options );
			$this->enqueue_styles( $this->options );
			$this->register_scripts( $this->options );
			$this->enqueue_scripts( $this->options );
		}
	}

	public function get_options( $atts ) {

		$options = array(
			'cdn'                  => isset( $settings['cdn_option'] ) ? $settings['cdn_option'] : false,
			'key'                  => ! empty ( $settings['key'] ) ? $settings['key'] : '',
			'fp_dir'               => ! empty ( $settings['directory'] ) ? $settings['directory'] : '',
			'asf_js'               => ! empty ( $settings['asf_js'] ) ? $settings['asf_js'] : false,
			'asf_css'              => ! empty ( $settings['asf_css'] ) ? $settings['asf_css'] : false,
			'vast_js'              => ! empty ( $settings['vast_js'] ) ? $settings['vast_js'] : false,
			'vast_css'             => ! empty ( $settings['vast_css'] ) ? $settings['vast_css'] : false,
			'suffix'               => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min',
			'hls_dep'              => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? array( 'flowplayer5-script', 'hlsjs' ) : array( 'flowplayer5-script' ),
			'is_hls'               => $flowplayer_shortcode->get_attr_value( 'application/x-mpegurl', $atts ) && $flowplayer_shortcode->get_attr_value( 'hls_plugin', $atts ),
			'is_lightbox'          => $flowplayer_shortcode->get_attr_value( 'lightbox', $atts ),
			'has_shortcode'        => $has_flowplayer_shortcode,
			'qualities'            => $flowplayer_shortcode->get_video_qualities( $atts ),
			'qs_dir'               => isset( $settings['fp_version'] ) && 'fp6' === $settings['fp_version'] ? 'quality-selector/flowplayer.' : 'drive/',
			'fp_6'                 => isset( $settings['fp_version'] ) && 'fp6' === $settings['fp_version'] ? '-v6' : '',
			'plugin_version'       => $plugin_version,
			'player_version'       => $player_version,
			'flowplayer_directory' => trailingslashit( $this->get_flowplayer_directory( $settings ) ),
			'assets_directory'     => trailingslashit( $this->get_assets_directory( $settings ) ),
		);

		return $options;
	}

	public function get_flowplayer_directory( $options = array()  ) {
		$flowplayer5_commercial = trailingslashit( WP_CONTENT_DIR ) . 'flowplayer-commercial';

		if ( is_file( $flowplayer5_commercial ) && ! $options['cdn'] && $options['key'] ) {
			$flowplayer5_directory = $flowplayer5_commercial;
		} elseif ( $options['fp_dir'] ) {
			$flowplayer5_directory = $options['fp_dir'];
		} elseif ( $options['cdn'] && ! $options['key'] ) {
			$flowplayer5_directory = plugins_url( '/assets/flowplayer' . $options['fp_6'], __FILE__  );
		} else {
			$flowplayer5_directory = '//releases.flowplayer.org/' . $options['player_version'] . '/'. ( $options['key'] ? 'commercial' : '' );
		}

		return apply_filters( 'flowplayer_directory', $flowplayer5_directory );
	}

	public function get_assets_directory( $options = array() ) {
		if ( $options['cdn'] ) {
			$assets_directory = '//releases.flowplayer.org/';
		} else {
			$assets_directory = plugins_url( '/assets', __FILE__ );
		}
		return apply_filters( 'fp5_assets_directory', $assets_directory );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function register_styles( $options = array() ) {
		// Register stylesheets
		wp_register_style( 'flowplayer5-skins', $options['flowplayer_directory'] . 'skin/all-skins.css', array(), $options['plugin_version'] );
		wp_register_style( 'flowplayer5-logo-origin', plugins_url( '/assets/css/public-concat' . $options['suffix'] . '.css', __FILE__ ), array(), $options['plugin_version'] );
		wp_register_style( 'flowplayer5-asf', esc_url( $options['asf_css'] ), array(), null );
		wp_register_style( 'flowplayer5-vast', esc_url( $options['vast_css'] ), array(), null );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $options = array() ) {
		wp_enqueue_style( 'flowplayer5-skins' );
		wp_enqueue_style( 'flowplayer5-logo-origin' );
		if ( $options['asf_css'] ) {
			wp_enqueue_style( 'flowplayer5-asf' );
		}
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function register_scripts( $options = array() ) {
		// Register JavaScript so that they can be enqueued later
 		wp_register_script( 'flowplayer5-script', $options['flowplayer_directory'] . 'flowplayer' . $options['suffix'] . '.js', array( 'jquery' ), $options['plugin_version'], false );
		wp_register_script( 'flowplayer5-ima3', '//s0.2mdn.net/instream/html5/ima3.js', array(), null, false );
		wp_register_script( 'flowplayer5-asf', esc_url( $options['asf_js'] ), array( 'flowplayer5-ima3' ), null, false );
		wp_register_script( 'flowplayer5-vast', esc_url( $options['vast_js'] ), array(), null, false );
		wp_register_script( 'hlsjs', $options['assets_directory'] . 'hlsjs/hls.js', array(), null, false );
		wp_register_script( 'flowplayer5-hlsjs', $options['assets_directory'] . 'hlsjs/flowplayer.hlsjs' . $options['suffix'] . '.js', $options['hls_dep'], '0456f85', false );
		wp_register_script( 'flowplayer5-quality-selector', $options['assets_directory'] . $options['qs_dir'] . 'quality-selector' . $options['suffix'] . '.js', array( 'flowplayer5-script' ), $options['plugin_version'], false );
		wp_register_script( 'magnific-popup', plugins_url( '/assets/magnific-popup/magnific-popup' . $options['suffix'] . '.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $options = array() ) {
		wp_enqueue_script( 'flowplayer5-script' );
		if ( $options['is_hls'] && $options['fp_6'] ){
			wp_enqueue_script( 'flowplayer5-hlsjs' );
		}
		if ( $options['asf_js'] ){
			wp_enqueue_script( 'flowplayer5-asf' );
		}
		if ( $options['qualities'] ){
			wp_enqueue_script( 'flowplayer5-quality-selector' );
		}
		if ( $options['is_lightbox'] ){
			wp_enqueue_script( 'magnific-popup' );
		}
	}

}
