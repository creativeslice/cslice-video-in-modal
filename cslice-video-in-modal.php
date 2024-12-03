<?php
/**
 * Plugin Name:       Creative Slice Video In Modal
 * Description:       Play YouTube or Vimeo video in modal.
 * Version:           2024.12.03
 * Requires at least: 6.6
 * Tested up to:      6.6.2
 * Requires PHP:      8.0
 * Author:            Creative Slice
 * License:           GPL-2.0-or-later
 * Text Domain:       cslice-video-in-modal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Plugin updater - PUBLIC REPO
 */
if (is_admin()) {
	require_once plugin_dir_path(__FILE__) . 'cslice-plugin-updater-public.php';
	new CSlice\VideoInModal\Plugin_Updater(
		__FILE__,
		'creativeslice/cslice-video-in-modal'
	);
}

if (!defined('CSLICE_VIDEO_IN_MODAL_VERSION')) {
	$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
    define('CSLICE_VIDEO_IN_MODAL_VERSION', $plugin_data['Version']);
}


/**
 * Register scripts & styles so they can be enqueued below
 */
// TODO: Use built-in versioning from asset php files.
function cslice_video_in_modal_enqueue_assets() {
	if (is_admin()) return;

	$stylesUrl = plugin_dir_url(__FILE__) . 'build/index.css' . '?v=' . CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION;
	wp_register_style('cslice-video-in-modal-styles', $stylesUrl, [], '', 'all');

	$scriptsUrl = plugin_dir_url(__FILE__) . 'build/index.js' . '?v=' . CSLICE_VIDEO_IN_MODAL_PLUGIN_VERSION;
	wp_register_script('cslice-video-in-modal-scripts', $scriptsUrl, [], '', ['strategy' => 'defer']);
}
add_action('wp_enqueue_scripts', 'cslice_video_in_modal_enqueue_assets');


/**
 * Add video data attribute to the wp-core-button block
 */
function cslice_video_in_modal_render_block_core_button($block_content, $block) {
	// Check if button has class of 'open-video-in-modal'
	if (!isset($block['attrs']['className']) || strpos($block['attrs']['className'], 'open-video-in-modal') === false) {
		return $block_content;
	}

	// Enqueue files
	wp_enqueue_style('cslice-video-in-modal-styles');
	wp_enqueue_script('cslice-video-in-modal-scripts');

	// Modify the button attributes using the HTML API
	$processor = new WP_HTML_Tag_Processor($block_content);

	if ($processor->next_tag('a')) {
		$url = $processor->get_attribute('href');
		if ($url) {

			// Vimeo URL parameters
			// https://vimeo.com/925983356
			if (strpos($url, 'vimeo.com') !== false) {
				$video_id = substr(parse_url($url, PHP_URL_PATH), 1);
				$url = "https://player.vimeo.com/video/$video_id?autoplay=1";

			// YouTube URL parameters
			// https://youtu.be/peJbhfeS6Zc
			// https://www.youtube.com/watch?v=peJbhfeS6Zc
			} elseif (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
				$video_id = '';
				if (strpos($url, 'youtube.com') !== false) {
					parse_str(parse_url($url, PHP_URL_QUERY), $query_params);
					$video_id = $query_params['v'] ?? '';
				} elseif (strpos($url, 'youtu.be') !== false) {
					$video_id = substr(parse_url($url, PHP_URL_PATH), 1);
				}
				if ($video_id) {
					$url = "https://www.youtube.com/embed/$video_id?modestbranding=1&autoplay=1&rel=0";
				}
			}
			$processor->set_attribute('data-iframe', esc_url($url));
		}
	}

	return $processor->get_updated_html();
}
add_filter('render_block_core/button', 'cslice_video_in_modal_render_block_core_button', 10, 2);


/**
 * Register Block Pattern - Video Modal Button
 */
// TODO: Move to a separate file in src directory
function cslice_video_in_modal_register_block_patterns() {
	if (function_exists('register_block_pattern')) {
		register_block_pattern('cslice/video-modal-button', array(
			'title'  	=> __('Vimeo YouTube Button', 'cslice-video-in-modal'),
			'description'=> __('Button to open a Vimeo or YouTube video in a modal popup.', 'cslice-video-in-modal'),
			'keywords'   => array('video', 'modal', 'button', 'youtube', 'vimeo', 'dialog', 'popup'),
			'categories' => array('media'),
			'content'	=> '<!-- wp:buttons -->
				<div class="wp-block-buttons"><!-- wp:button {"className":"open-video-in-modal"} -->
				<div class="wp-block-button open-video-in-modal"><a class="wp-block-button__link" href="">Open Video</a></div>
				<!-- /wp:button --></div>
				<!-- /wp:buttons -->',
		));
	}
}
add_action('init', 'cslice_video_in_modal_register_block_patterns');
