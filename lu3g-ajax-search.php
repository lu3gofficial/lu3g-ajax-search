<?php
/**
 * Plugin Name:       LU3G Ajax Search
 * Description:       Widget Elementor di ricerca Ajax per CPT (JetEngine compatibile): lente che apre un pannello di ricerca con effetto blur, risultati live e link al dettaglio.
 * Version:           1.5.0
 * Plugin URI:        https://github.com/lu3gofficial/lu3g-ajax-search
 * Update URI:        https://github.com/lu3gofficial/lu3g-ajax-search
 * Author:            LU3G Agenzia Web
 * Text Domain:       lu3g-ajax-search
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 */

defined( 'ABSPATH' ) || exit;

define( 'LU3G_SEARCH_VERSION', '1.5.0' );
define( 'LU3G_SEARCH_FILE', __FILE__ );
define( 'LU3G_SEARCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'LU3G_SEARCH_URL', plugin_dir_url( __FILE__ ) );

require_once LU3G_SEARCH_DIR . 'includes/class-lu3g-search-config.php';
require_once LU3G_SEARCH_DIR . 'includes/class-lu3g-search-rest.php';

/**
 * Endpoint REST: /wp-json/lu3g-search/v1/search
 */
add_action( 'rest_api_init', array( 'Lu3g_Search_Rest', 'register' ) );

/**
 * Asset: registrati qui, caricati da Elementor solo dove c'è il widget.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style(
		'lu3g-ajax-search',
		LU3G_SEARCH_URL . 'assets/css/lu3g-ajax-search.css',
		array(),
		LU3G_SEARCH_VERSION
	);

	wp_register_script(
		'lu3g-ajax-search',
		LU3G_SEARCH_URL . 'assets/js/lu3g-ajax-search.js',
		array(),
		LU3G_SEARCH_VERSION,
		true
	);

	wp_add_inline_script(
		'lu3g-ajax-search',
		'window.LU3G_SEARCH = ' . wp_json_encode( array(
			'restUrl' => esc_url_raw( rest_url( Lu3g_Search_Rest::NAMESPACE_V1 . '/search' ) ),
		) ) . ';',
		'before'
	);
} );

/**
 * Widget Elementor.
 */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once LU3G_SEARCH_DIR . 'widgets/class-lu3g-ajax-search-widget.php';
	$widgets_manager->register( new Lu3g_Ajax_Search_Widget() );
} );

/**
 * Cache dei risultati: ogni modifica ai contenuti invalida tutte le ricerche salvate.
 */
function lu3g_search_bump_cache() {
	update_option( 'lu3g_search_cache_v', (string) microtime( true ) );
}

add_action( 'save_post', function ( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	lu3g_search_bump_cache();
} );
add_action( 'deleted_post', 'lu3g_search_bump_cache' );
add_action( 'trashed_post', 'lu3g_search_bump_cache' );
add_action( 'edited_term', 'lu3g_search_bump_cache' );
add_action( 'set_object_terms', 'lu3g_search_bump_cache' );

/**
 * Avviso se Elementor non è attivo.
 */
add_action( 'admin_notices', function () {
	if ( did_action( 'elementor/loaded' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>LU3G Ajax Search</strong> richiede Elementor attivo.</p></div>';
} );

/**
 * Aggiornamenti automatici dalle release GitHub di https://github.com/lu3gofficial/lu3g-ajax-search (vedi includes/class-updater.php).
 * Fuori da ogni controllo su Elementor: il plugin si aggiorna anche dove Elementor è disattivato.
 */
require_once __DIR__ . '/includes/class-updater.php';
LU3G_Ajax_Search_Updater::init( __FILE__ );
