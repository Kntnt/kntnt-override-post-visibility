<?php
/**
 * @package Kntnt\OverridePostVisibility
 * Plugin Name:       Kntnt Override Post Visibility
 * Description:       Adds the option to make pending, scheduled, or private posts visible to anyone with the URL, optional adding automatic notification and/or noindex meta tag.
 * Version:           1.0.0
 * Tags:              visibility, post status, pending, scheduled, private
 * Plugin URI:        https://github.com/Kntnt/kntnt-override-post-visibility
 * Tested up to: 6.7
 * Requires at least: 6.7
 * Requires PHP:      8.3
 * Author:            TBarregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       kntnt-override-post-visibility
 * Domain Path:       /languages
 */

namespace Kntnt\OverridePostVisibility;

// Don't allow direct access
defined( 'ABSPATH' ) && new Plugin;

class Plugin {

	public function __construct() {

		// Registers the post meta fields used by the plugin
		add_action( 'init', [ $this, 'add_visibility_meta_fields' ] );

		// Add visibility option in block editor
		add_action( 'enqueue_block_editor_assets', [ $this, 'add_visibility_option_in_editor' ] );

		// Add visibility option in both Quick Edit and Bulk Edit
		add_action( 'admin_enqueue_scripts', [ $this, 'add_visibility_option_in_admin' ] );

		// Save visibility option in both Quick Edit and Bulk Edit
		add_action( 'save_post', [ $this, 'save_visibility_option_in_admin' ] );

		// Modifies the WordPress query to show pending/scheduled/private posts if the visibility is overridden
		add_filter( 'the_posts', [ $this, 'allow_posts_access' ], 10, 2 );

		// Add noindex meta tag to pending and scheduled posts and possible to private posts also 
		add_action( 'wp_head', [ $this, 'add_noindex' ] );

		// Add warning notice
		add_filter( 'the_content', [ $this, 'add_notice' ] );

		// Modify title prefix
		add_filter( 'private_title_format', [ $this, 'modify_title_prefix' ] );

		// Register style to allow it to be enqueued at a later point.
		add_action( 'wp_enqueue_scripts', [ $this, 'load_css' ] );

		// Load text domain for translations
		add_action( 'plugins_loaded', [ $this, 'load_text_domain' ] );

	}

	/**
	 * Registers the post meta fields used by the plugin
	 */
	public function add_visibility_meta_fields() {

		$post_types = get_post_types( [ 'public' => true ] );

		$post_types = apply_filters( 'kntnt_override_post_visibility_post_types', $post_types );

		foreach ( $post_types as $post_type ) {

			register_post_meta( $post_type, 'kntnt_override_post_visibility', [
				'single' => true,
				'type' => 'boolean',
				'default' => false,
				'description' => 'Whether or not to override visibility',
				'revisions_enabled' => false,
				'show_in_rest' => true,
			] );

			register_post_meta( $post_type, 'kntnt_override_post_visibility_alert', [
				'single' => true,
				'type' => 'boolean',
				'default' => true,
				'description' => 'Whether or not to add an alert to visitors',
				'revisions_enabled' => false,
				'show_in_rest' => true,
			] );

			register_post_meta( $post_type, 'kntnt_override_post_visibility_noindex', [
				'single' => true,
				'type' => 'boolean',
				'default' => true,
				'description' => 'Whether or not to add noindex meta tag',
				'revisions_enabled' => false,
				'show_in_rest' => true,
			] );

		}

	}

	/**
	 * Add visibility option in block editor
	 */
	public function add_visibility_option_in_editor(): void {
		$asset_file = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';
		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
			wp_enqueue_script( 'kntnt-override-post-visibility.js', plugin_dir_url( __FILE__ ) . 'build/index.js', $asset['dependencies'], $asset['version'], true );
			wp_set_script_translations( 'kntnt-override-post-visibility.js', 'kntnt-override-post-visibility' );
		}
	}

	/**
	 * Add visibility option in both Quick Edit and Bulk Edit
	 */
	public function add_visibility_option_in_admin( string $hook ): void {

		// Abort if not a post listing page
		if ( $hook !== 'edit.php' ) {
			return;
		}

		// Get the current screen to find current post type
		$screen = get_current_screen();
		if ( ! $screen || ! isset( $screen->post_type ) ) {
			return;
		}

		// Enqueue script
		$js_url = plugin_dir_url( __FILE__ ) . 'js/kntnt-override-post-visibility-admin.js';
		$js_file = plugin_dir_path( __FILE__ ) . 'js/kntnt-override-post-visibility-admin.js';
		wp_enqueue_script( 'kntnt-override-post-visibility-admin.js', $js_url, [ 'jquery', 'wp-api' ], filemtime( $js_file ), true );

		// Table of visibility override vales for posts currently displayed on the screen
		$visibility = [];

		global $wp_query;
		if ( $wp_query->posts ) {

			// Get the IDs of posts currently displayed on the screen
			$current_posts = wp_list_pluck( $wp_query->posts, 'ID' );

			// Only process posts that have a status we care about
			$posts = get_posts( [
				                    'post_type' => $screen->post_type,
				                    'post__in' => $current_posts,
				                    'post_status' => [ 'pending', 'future', 'private' ],
				                    'posts_per_page' => - 1,
				                    'fields' => 'ids',
			                    ] );

			// Create metadata array for JavaScript
			foreach ( $posts as $post_id ) {
				$visibility[ $post_id ] = [
					'override' => (bool) get_post_meta( $post_id, 'kntnt_override_post_visibility', true ),
					'alert' => (bool) get_post_meta( $post_id, 'kntnt_override_post_visibility_alert', true ),
					'noindex' => (bool) get_post_meta( $post_id, 'kntnt_override_post_visibility_noindex', true ),
				];
			}

		}

		// Localize script with labels and options
		wp_localize_script( 'kntnt-override-post-visibility-admin.js', 'kntnt_override_post_visibility', [
			'label' => __( "Override visibility", 'kntnt-override-post-visibility' ),
			'options' => [
				'-1' => __( "— No Change —", 'kntnt-override-post-visibility' ),
				'off' => __( "No", 'kntnt-override-post-visibility' ),
				'on_with_alert_with_noindex' => __( "Yes, with automatic visitor alert and noindex meta tag", 'kntnt-override-post-visibility' ),
				'on_with_noindex' => __( "Yes, with noindex meta tag", 'kntnt-override-post-visibility' ),
				'on_with_alert' => __( "Yes, with automatic visitor alert", 'kntnt-override-post-visibility' ),
				'on' => __( "Yes, without extras", 'kntnt-override-post-visibility' ),
			],
			'visibility' => $visibility,
		] );

	}

	/**
	 * Save the archive status in both Quick Edit and Bulk Edit
	 */
	public function save_visibility_option_in_admin( int $post_id ): int {

		// If saving the previous version or autosaving, don't do anything
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check that the kntnt_override_post_visibility field exists and has a value that is not -1 (WordPress convention for 'No Change')
		if ( ! isset( $_REQUEST['kntnt_override_post_visibility'] ) || $_REQUEST['kntnt_override_post_visibility'] === '-1' ) {
			return $post_id;
		}

		// $value will be one of following strings:
		//   - 'off'                        - don't alter WordPress visibility (default)
		//   - 'on_with_alert_with_noindex' - alter WordPress visibility, alert visitors, add noindex meta tag
		//   - 'on_with_noindex'            - alter WordPress visibility, add noindex meta tag
		//   - 'on_with_alert'              - alter WordPress visibility, alert visitors
		//   - 'on'                         - alter WordPress visibility
		$value = $_REQUEST['kntnt_override_post_visibility'];

		update_post_meta( $post_id, 'kntnt_override_post_visibility', str_contains( $value, 'on' ) );
		update_post_meta( $post_id, 'kntnt_override_post_visibility_alert', str_contains( $value, 'alert' ) );
		update_post_meta( $post_id, 'kntnt_override_post_visibility_noindex', str_contains( $value, 'noindex' ) );

		return $post_id;

	}

	/**
	 * Modifies the WordPress query to show pending/scheduled/private posts if the visibility is overridden
	 */
	public function allow_posts_access( array $posts, object $query ): array {

		// Abort if posts already are found, or we are in admin
		if ( ! empty( $posts ) || is_admin() ) {
			return $posts;
		}

		// Get the current URL path
		$url_path = trim( parse_url( add_query_arg( [] ), PHP_URL_PATH ), '/' );
		$url_parts = explode( '/', $url_path );
		$slug = end( $url_parts );

		// Retrieve a post with this permalink part and the status pending/scheduled/private.
		$overridden_posts = get_posts( [
			                               'name' => $slug,
			                               'post_type' => get_post_types( [ 'public' => true ] ),
			                               'post_status' => [ 'pending', 'future', 'private' ],
			                               'numberposts' => 1,
		                               ] );

		// Abort if no overridden post.
		if ( ! $overridden_posts || ! $this->is_overridden( $overridden_posts[0]->ID ) ) {
			return $posts;
		}

		// Tell WordPress this is a request for a singular content item (triggers singular content layout)
		$query->is_singular = true;

		// Determine the correct singular query flag based on post type
		if ( $overridden_posts[0]->post_type == 'page' ) {
			$query->is_page = true;
		}
		else {
			$query->is_single = true;
		}

		// Override the default 404 behavior for unpublished content
		$query->is_404 = false;

		return $overridden_posts;

	}

	/**
	 * Add noindex meta tag to posts
	 */
	public function add_noindex(): void {
		$post = get_post();
		if ( $post && is_singular( $post ) && $this->is_overridden( $post->ID ) && $this->is_with_noindex( $post->ID ) ) {
			echo '<meta name="robots" content="noindex" />';
		}
	}

	/**
	 * Display warning banner on archived posts
	 */
	public function add_notice( string $content ): string {

		$post = get_post();

		// Abort if no notice should be added.
		if ( ! $post || ! is_singular( $post ) || ! $this->is_overridden( $post->ID ) || ! $this->is_with_alert( $post->ID ) ) {
			return $content;
		}

		switch ( $post->post_status ) {
			case 'pending':
				$notice = __( 'This post is pending review.', 'kntnt-override-post-visibility' );
				$status_class = 'status-pending';
				break;
			case 'future':
				if ( $scheduled_date_time = get_post_datetime( $post, 'date' ) ) {
					$timestamp = $scheduled_date_time->getTimestamp();
					$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );
					$formatted_time = date_i18n( get_option( 'time_format' ), $timestamp );
					$notice = sprintf( __( 'This post is scheduled to be published on %s at %s.', 'kntnt-override-post-visibility' ), $formatted_date, $formatted_time );
				}
				else {
					$notice = __( 'This post is scheduled for future publication.', 'kntnt-override-post-visibility' );
				}
				$status_class = 'status-future';
				break;
			case 'private':
				$notice = __( 'This content is outdated. It is available for reference only.', 'kntnt-override-post-visibility' );
				$status_class = 'status-private';
				break;
			default:
				$notice = '';
				$status_class = '';
		}

		wp_enqueue_style( 'kntnt-override-post-visibility.css' );
		$notice = '<strong>' . __( 'NOTE', 'kntnt-override-post-visibility' ) . '</strong> ' . $notice;
		$content = '<div class="kntnt-override-post-visibility ' . $status_class . '">' . $notice . '</div>' . $content;

		return $content;

	}

	/**
	 * Modify title prefix
	 */
	public function modify_title_prefix( string $title ): string {
		$post = get_post();
		if ( $post && $this->is_overridden( $post->ID ) && $this->is_with_alert( $post->ID ) ) {
			$title = '%s';
		}
		return $title;
	}

	/**
	 * Register style to allow it to be enqueued at a later point.
	 */
	public function load_css(): void {
		$css_url = plugin_dir_url( __FILE__ ) . 'css/kntnt-override-post-visibility.css';
		$css_file = plugin_dir_path( __FILE__ ) . 'css/kntnt-override-post-visibility.css';
		wp_register_style( 'kntnt-override-post-visibility.css', $css_url, [], filemtime( $css_file ) );
	}

	/**
	 * Load text domain for translations
	 */
	public function load_text_domain(): void {
		load_plugin_textdomain( 'kntnt-override-post-visibility', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Whether the post is overridden
	 */
	private function is_overridden( int $post_id ): bool {
		return get_post_meta( $post_id, 'kntnt_override_post_visibility', true );
	}

	/**
	 * Whether to add noindex or not
	 */
	private function is_with_noindex( int $post_id ): bool {
		return get_post_meta( $post_id, 'kntnt_override_post_visibility_noindex', true );
	}

	/**
	 * Whether to alert or not the visitor
	 */
	private function is_with_alert( int $post_id ): bool {
		return get_post_meta( $post_id, 'kntnt_override_post_visibility_alert', true );
	}

}