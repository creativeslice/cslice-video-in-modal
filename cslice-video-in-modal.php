<?php
/**
 * Plugin Name:       Creative Slice Video In Modal
 * Description:       Open video in modal.
 * Requires at least: 6.4
 * Tested up to:      6.6.2
 * Requires PHP:      8.0
 * Version:           0.1.0
 * Author:            Creative Slice
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cslice-video-in-modal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Retrieve plugin version from the plugin header
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
$plugin_version = $plugin_data['Version'];


// Define plugin version constant
if (!defined('CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION')) {
	define('CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION', $plugin_version);
}


/**
 * Frontend styles & scripts
 */
function cslice_video_in_modal_enqueue_assets() {
	if ( is_admin() ) {
		return; // Stop if in admin
	}

	$styles = 'assets/cslice-video-in-modal.css';
	$stylesUrl = plugin_dir_url(__FILE__) . $styles . '?v=' . CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION;
	wp_register_style('cslice-video-in-modal-styles', $stylesUrl, [], '', 'all');

	$scripts = 'assets/cslice-video-in-modal.js';
	$scriptsUrl = plugin_dir_url(__FILE__) . $scripts . '?v=' . CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION;
	wp_register_script('cslice-video-in-modal-scripts', $scriptsUrl, [], '', ['strategy' => 'defer']);
}
add_action('wp_enqueue_scripts', 'cslice_video_in_modal_enqueue_assets');


/**
 * Enqueue files if class exists on button
 */
function cslice_video_in_modal_render_block_core_button( $block_content, $block ) {
	if ( strpos($block_content, 'youtube') !== false || strpos($block_content, 'vimeo') !== false ) {
		// Enqueue files
		wp_enqueue_style('cslice-video-in-modal-styles');
		wp_enqueue_script('cslice-video-in-modal-scripts');

		// Add class for js
		$block_content = new WP_HTML_Tag_Processor( $block_content );
		$block_content->next_tag(); /* first tag should always be ul or ol */
		$block_content->add_class( 'open-video-in-modal' );
		$block_content->get_updated_html();
	}

	return $block_content;
}
add_action( 'render_block_core/button', 'cslice_video_in_modal_render_block_core_button', 10, 2 );
