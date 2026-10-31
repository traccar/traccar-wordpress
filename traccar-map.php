<?php
/**
 * Plugin Name: Traccar Map
 * Description: Embed the Traccar web app using a public read-only account token.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: traccar-map
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Traccar_Map {
	const OPTION = 'traccar_map_settings';
	const PAGE   = 'traccar-map';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_shortcode( 'traccar_map', array( __CLASS__, 'shortcode' ) );
	}

	public static function settings() {
		$stored = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $stored ) ? $stored : array(), array(
			'url' => '', 'token' => '', 'height' => '600px',
		) );
	}

	public static function admin_menu() {
		add_options_page( __( 'Traccar Map', 'traccar-map' ), __( 'Traccar Map', 'traccar-map' ), 'manage_options', self::PAGE, array( __CLASS__, 'settings_page' ) );
	}

	public static function register_settings() {
		register_setting( self::OPTION, self::OPTION, array(
			'type' => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			'show_in_rest' => false,
		) );
		add_settings_section( 'connection', __( 'Public map', 'traccar-map' ), '__return_false', self::PAGE );
		foreach ( array(
			'url' => __( 'Server URL', 'traccar-map' ),
			'token' => __( 'Public read-only token', 'traccar-map' ),
			'height' => __( 'Default map height', 'traccar-map' ),
		) as $key => $label ) {
			add_settings_field( $key, $label, array( __CLASS__, 'field' ), self::PAGE, 'connection', array( 'label_for' => 'traccar-map-' . $key, 'key' => $key ) );
		}
	}

	/** Accept only a web app base URL, including optional port and subdirectory. */
	public static function normalize_url( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$value = trim( $value );
		$parts = wp_parse_url( $value );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['scheme'] )
			|| ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true )
			|| isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] )
			|| preg_match( '/[\x00-\x20<>"\\\\]/', $value ) ) {
			return '';
		}
		return trailingslashit( esc_url_raw( $value, array( 'http', 'https' ) ) );
	}

	/** Limit CSS to a positive number and an explicit supported unit. */
	public static function normalize_height( $value ) {
		if ( ! is_scalar( $value ) || ! preg_match( '/^([0-9]+(?:\.[0-9]+)?)(px|vh|rem|em)?$/i', trim( (string) $value ), $matches ) ) {
			return '';
		}
		if ( (float) $matches[1] <= 0 || (float) $matches[1] > 10000 ) {
			return '';
		}
		return $matches[1] . ( isset( $matches[2] ) ? strtolower( $matches[2] ) : 'px' );
	}

	public static function sanitize_settings( $input ) {
		$previous = self::settings();
		if ( ! is_array( $input ) ) {
			return $previous;
		}
		$result = $previous;
		foreach ( array( 'url', 'token', 'height' ) as $key ) {
			if ( ! isset( $input[ $key ] ) || ! is_string( $input[ $key ] ) ) {
				continue;
			}
			$value = trim( $input[ $key ] );
			if ( 'url' === $key ) {
				$clean = self::normalize_url( $value );
				$valid = '' === $value || '' !== $clean;
			} elseif ( 'height' === $key ) {
				$clean = self::normalize_height( $value );
				$valid = '' !== $clean;
			} else {
				// Preserve the opaque token exactly; it is URL-encoded when rendered.
				$clean = $value;
				$valid = ! preg_match( '/[\x00-\x20\x7f]/', $value );
			}
			if ( $valid ) {
				$result[ $key ] = $clean;
			} else {
				add_settings_error( self::OPTION, 'invalid_' . $key, sprintf(
					/* translators: %s: setting name. */
					__( 'Invalid %s. The previous value was kept.', 'traccar-map' ), $key
				) );
			}
		}
		return $result;
	}

	public static function field( $args ) {
		$key = $args['key'];
		$settings = self::settings();
		$descriptions = array(
			'url' => __( 'Web app base URL, for example https://tracking.example.com/ or https://example.com/traccar/. Do not include a query string or token. Use HTTPS on HTTPS sites.', 'traccar-map' ),
			'token' => __( 'Visible to every map visitor. Use only a dedicated non-admin Traccar account with Read-Only enabled and access limited to public devices. The plugin cannot enforce account permissions.', 'traccar-map' ),
			'height' => __( 'Positive number in px, vh, rem or em (maximum 10000). A number without a unit uses pixels. Default: 600px.', 'traccar-map' ),
		);
		printf( '<input class="regular-text" type="%1$s" id="traccar-map-%2$s" name="%3$s[%2$s]" value="%4$s" aria-describedby="traccar-map-%2$s-help" autocomplete="off" spellcheck="false">',
			'url' === $key ? 'url' : 'text', esc_attr( $key ), esc_attr( self::OPTION ), esc_attr( $settings[ $key ] ) );
		printf( '<p class="description" id="traccar-map-%1$s-help">%2$s</p>', esc_attr( $key ), esc_html( $descriptions[ $key ] ) );
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Traccar Map', 'traccar-map' ); ?></h1>
			<p><?php esc_html_e( 'Embed your existing Traccar web app with a public read-only token.', 'traccar-map' ); ?></p>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
			<h2><?php esc_html_e( 'Usage', 'traccar-map' ); ?></h2>
			<p><?php esc_html_e( 'Add a Shortcode block to a page or post:', 'traccar-map' ); ?> <code>[traccar_map]</code></p>
			<p><?php esc_html_e( 'Override the height for one map:', 'traccar-map' ); ?> <code>[traccar_map height="80vh"]</code></p>
			<p><?php esc_html_e( 'Your Traccar server must allow iframe embedding from this site. Browser cookie restrictions can affect embedded login.', 'traccar-map' ); ?></p>
		</div>
		<?php
	}

	public static function shortcode( $attributes = array() ) {
		$settings = self::settings();
		$url = self::normalize_url( $settings['url'] );
		$token = is_string( $settings['token'] ) ? trim( $settings['token'] ) : '';
		if ( '' === $url || '' === $token ) {
			return current_user_can( 'manage_options' )
				? '<p>' . esc_html__( 'Configure the server URL and public token in Settings → Traccar Map.', 'traccar-map' ) . '</p>' : '';
		}
		$attributes = shortcode_atts( array( 'height' => $settings['height'] ), $attributes, 'traccar_map' );
		$height = self::normalize_height( $attributes['height'] );
		if ( '' === $height ) {
			$height = self::normalize_height( $settings['height'] );
		}
		if ( '' === $height ) {
			$height = '600px';
		}
		$src = add_query_arg( 'token', rawurlencode( $token ), $url );
		return sprintf(
			'<iframe class="traccar-map" src="%1$s" title="%2$s" width="100%%" style="display:block;width:100%%;height:%3$s;border:0;" loading="lazy" referrerpolicy="no-referrer" allowfullscreen></iframe>',
			esc_url( $src ), esc_attr__( 'Traccar public map', 'traccar-map' ), esc_attr( $height )
		);
	}
}

Traccar_Map::init();
