<?php
/**
 * Endpoint REST della ricerca.
 *
 * Ordine dei risultati:
 * 1. SKU dei prodotti WooCommerce, varianti comprese (un codice è la ricerca più precisa)
 * 2. corrispondenze in titolo / contenuto (rilevanza di WordPress, titolo prima)
 * 3. corrispondenze nei meta field indicati (es. campi JetEngine)
 * 4. prodotti dei termini di tassonomia che corrispondono (es. "sirena")
 */

defined( 'ABSPATH' ) || exit;

class Lu3g_Search_Rest {

	const NAMESPACE_V1 = 'lu3g-search/v1';
	const CACHE_TTL    = 10 * MINUTE_IN_SECONDS;

	public static function register() {
		register_rest_route( self::NAMESPACE_V1, '/search', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'search' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'c' => array(
					'required' => true,
					'type'     => 'string',
				),
				's' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );
	}

	public static function search( WP_REST_Request $request ) {
		$config = Lu3g_Search_Config::verify( $request['c'], $request['s'] );

		if ( null === $config ) {
			return new WP_Error( 'lu3g_search_invalid', 'Configurazione di ricerca non valida.', array( 'status' => 403 ) );
		}

		$q = trim( mb_substr( (string) $request['q'], 0, 100 ) );

		if ( mb_strlen( $q ) < $config['min'] ) {
			return rest_ensure_response( array( 'results' => array() ) );
		}

		$cache_key = 'lu3g_s_' . md5( LU3G_SEARCH_VERSION . '|' . get_option( 'lu3g_search_cache_v', '1' ) . '|' . $request['c'] . '|' . self::lang() . '|' . mb_strtolower( $q ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return rest_ensure_response( array( 'results' => $cached ) );
		}

		$ids     = self::find_ids( $q, $config );
		$results = array_map( function ( $id ) use ( $config ) {
			return self::build_item( $id, $config );
		}, $ids );

		set_transient( $cache_key, $results, self::CACHE_TTL );

		return rest_ensure_response( array( 'results' => $results ) );
	}

	/**
	 * Cerca gli ID nell'ordine: SKU → titolo/contenuto → meta → tassonomie.
	 */
	private static function find_ids( $q, array $config ) {
		$types = array_values( array_filter( $config['types'], function ( $type ) {
			return post_type_exists( $type ) && is_post_type_viewable( $type );
		} ) );

		if ( empty( $types ) ) {
			return array();
		}

		$limit = $config['limit'];

		// 2. Titolo, riassunto e contenuto.
		$query = new WP_Query( array(
			'post_type'           => $types,
			'post_status'         => 'publish',
			's'                   => $q,
			'posts_per_page'      => $limit,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );
		$ids = array_map( 'intval', $query->posts );

		// Lo SKU va in testa: i risultati del titolo non devono nascondere il prodotto con quel codice.
		if ( ! empty( $config['sku'] ) && in_array( 'product', $types, true ) ) {
			$ids = array_values( array_unique( array_merge( self::search_sku( $q, $limit ), $ids ) ) );
		}

		// 3. Meta field (es. campi JetEngine semplici: testo, select, numero).
		if ( count( $ids ) < $limit && ! empty( $config['meta'] ) ) {
			$ids = array_merge( $ids, self::search_meta( $q, $types, $config['meta'], $limit ) );
		}

		// 4. Termini di tassonomia.
		if ( count( array_unique( $ids ) ) < $limit && $config['terms'] ) {
			$ids = array_merge( $ids, self::search_terms( $q, $types, $limit ) );
		}

		$ids = array_values( array_unique( $ids ) );

		if ( empty( $ids ) ) {
			return array();
		}

		// Passaggio finale con WP_Query: rispetta stato, lingua (WPML/Polylang) e ordine.
		$final = new WP_Query( array(
			'post_type'           => $types,
			'post_status'         => 'publish',
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => $limit,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );

		return array_map( 'intval', $final->posts );
	}

	/** SKU WooCommerce: anche solo una parte del codice. Una variante porta al suo prodotto. */
	private static function search_sku( $q, $limit ) {
		global $wpdb;

		$like = '%' . $wpdb->esc_like( $q ) . '%';

		$sql = $wpdb->prepare(
			"SELECT CASE WHEN p.post_type = 'product_variation' THEN p.post_parent ELSE p.ID END
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('product', 'product_variation')
			AND pm.meta_key = '_sku'
			AND pm.meta_value LIKE %s
			LIMIT %d",
			$like,
			$limit * 3
		);

		return array_values( array_unique( array_map( 'intval', (array) $wpdb->get_col( $sql ) ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function search_meta( $q, array $types, array $keys, $limit ) {
		global $wpdb;

		$type_ph = implode( ',', array_fill( 0, count( $types ), '%s' ) );
		$key_ph  = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
		$like    = '%' . $wpdb->esc_like( $q ) . '%';

		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$sql = $wpdb->prepare(
			"SELECT DISTINCT p.ID
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
			WHERE p.post_status = 'publish'
			AND p.post_type IN ($type_ph)
			AND pm.meta_key IN ($key_ph)
			AND pm.meta_value LIKE %s
			ORDER BY p.post_title ASC
			LIMIT %d",
			array_merge( $types, $keys, array( $like, $limit * 2 ) )
		);

		return array_map( 'intval', (array) $wpdb->get_col( $sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	private static function search_terms( $q, array $types, $limit ) {
		$taxonomies = array_values( array_filter( get_object_taxonomies( $types ), function ( $tax ) {
			$object = get_taxonomy( $tax );
			return $object && $object->public;
		} ) );

		if ( empty( $taxonomies ) ) {
			return array();
		}

		$terms = get_terms( array(
			'taxonomy'   => $taxonomies,
			'name__like' => $q,
			'hide_empty' => true,
			'number'     => 10,
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$by_tax = array();
		foreach ( $terms as $term ) {
			$by_tax[ $term->taxonomy ][] = (int) $term->term_id;
		}

		$tax_query = array( 'relation' => 'OR' );
		foreach ( $by_tax as $tax => $term_ids ) {
			$tax_query[] = array(
				'taxonomy' => $tax,
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);
		}

		$query = new WP_Query( array(
			'post_type'           => $types,
			'post_status'         => 'publish',
			'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'posts_per_page'      => $limit,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );

		return array_map( 'intval', $query->posts );
	}

	private static function build_item( $id, array $config ) {
		$sub = '';

		if ( $config['subtax'] && taxonomy_exists( $config['subtax'] ) ) {
			$terms = get_the_terms( $id, $config['subtax'] );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$sub = html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' );
			}
		}

		return array(
			'id'    => $id,
			'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $id ),
			'thumb' => $config['thumb'] ? (string) get_the_post_thumbnail_url( $id, 'thumbnail' ) : '',
			'sub'   => $sub,
		);
	}

	/**
	 * Lingua corrente (WPML / Polylang), per non mescolare la cache tra lingue.
	 */
	private static function lang() {
		$lang = apply_filters( 'wpml_current_language', null );
		if ( ! $lang && function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
		}
		return $lang ? (string) $lang : '';
	}
}
