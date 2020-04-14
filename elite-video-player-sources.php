<?php
/**
 * Plugin Name: Elite Video Player Sources
 * Plugin URI: https://nsukonny.ru/elite-video-player-sources
 * Description: Extend Elite video player and pick checked sources from sources list for show.
 * Version: 1.0.0
 * Author: NSukonny
 * Author URI: https://nsukonny.ru
 * Text Domain: elite-video-player-sources
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Elite_Video_Player_Sources' ) ) {

	include_once dirname( __FILE__ ) . '/libraries/elite-video-player-sources.php';

}


/**
 * The main function for returning Elite_Video_Player_Sources instance
 *
 * @since 1.0.0
 *
 * @return bool|object
 */
function elite_video_player_sources_runner() {

	if ( function_exists( 'Elite_player_shortcode' ) ) {
		return Elite_Video_Player_Sources::instance();
	}

	return false;
}

add_action( 'init', 'elite_video_player_sources_runner' );