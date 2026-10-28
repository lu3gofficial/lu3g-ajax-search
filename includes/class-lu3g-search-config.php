<?php
/**
 * Configurazione della ricerca, firmata lato server.
 *
 * Il widget stampa la configurazione (post type, meta field, ecc.) nel markup
 * insieme a una firma HMAC. L'endpoint REST accetta solo configurazioni firmate,
 * così nessuno può modificarle dal browser per cercare in campi o post type
 * diversi da quelli impostati in Elementor.
 */

defined( 'ABSPATH' ) || exit;

class Lu3g_Search_Config {

	/**
	 * Restituisce [payload, firma].
	 */
	public static function sign( array $config ) {
		$payload = base64_encode( wp_json_encode( self::normalize( $config ) ) );
		return array( $payload, self::hash( $payload ) );
	}

	/**
	 * Verifica la firma e restituisce la configurazione, oppure null.
	 */
	public static function verify( $payload, $signature ) {
		if ( ! is_string( $payload ) || ! is_string( $signature ) || '' === $payload ) {
			return null;
		}

		if ( ! hash_equals( self::hash( $payload ), $signature ) ) {
			return null;
		}

		$config = json_decode( base64_decode( $payload, true ), true );

		return is_array( $config ) ? self::normalize( $config ) : null;
	}

	/**
	 * Valori ripuliti e con limiti di sicurezza.
	 */
	public static function normalize( array $config ) {
		$types = isset( $config['types'] ) ? (array) $config['types'] : array();
		$meta  = isset( $config['meta'] ) ? (array) $config['meta'] : array();

		return array(
			'types'  => array_values( array_filter( array_map( 'sanitize_key', $types ) ) ),
			'meta'   => array_values( array_filter( array_map( 'sanitize_key', $meta ) ) ),
			'terms'  => ! empty( $config['terms'] ),
			'subtax' => isset( $config['subtax'] ) ? sanitize_key( $config['subtax'] ) : '',
			'thumb'  => ! empty( $config['thumb'] ),
			'limit'  => max( 1, min( 20, (int) ( $config['limit'] ?? 8 ) ) ),
			'min'    => max( 1, min( 5, (int) ( $config['min'] ?? 2 ) ) ),
		);
	}

	private static function hash( $payload ) {
		return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}
}
