<?php
/**
 * Endpoint del cotizador: POST /wp-json/fc/v1/cotizacion (multipart/form-data).
 *
 * Protección: nonce propio, campo trampa (honeypot), tiempo mínimo de llenado y límite de envíos por IP.
 * Valida todo de nuevo en el servidor y guarda el logo fuera del alcance público.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FC_LOGO_MAX_BYTES = 10485760; // 10 MB.

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'fc/v1',
			'/cotizacion',
			array(
				'methods'             => 'POST',
				'callback'            => 'fc_rest_cotizacion',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/** Datos que el tema entrega al JavaScript del cotizador. */
function fc_cotizador_datos_js() {
	return array(
		'endpoint' => esc_url_raw( rest_url( 'fc/v1/cotizacion' ) ),
		'nonce'    => wp_create_nonce( 'fc_cotizacion' ),
		'maxBytes' => FC_LOGO_MAX_BYTES,
	);
}

function fc_error( $mensaje, $campo = '', $status = 400 ) {
	return new WP_REST_Response(
		array(
			'ok'      => false,
			'mensaje' => $mensaje,
			'campo'   => $campo,
		),
		$status
	);
}

function fc_rest_cotizacion( WP_REST_Request $req ) {
	$p = $req->get_body_params();
	$g = function ( $k ) use ( $p ) {
		return isset( $p[ $k ] ) ? trim( (string) wp_unslash( $p[ $k ] ) ) : '';
	};

	/* Seguridad básica. */
	if ( ! wp_verify_nonce( $g( 'fc_nonce' ), 'fc_cotizacion' ) ) {
		return fc_error( 'La página estuvo abierta mucho rato. Recárgala y vuelve a enviar (no perderás lo que escribiste).', '', 403 );
	}
	if ( '' !== $g( 'sitio_web' ) ) { // Campo trampa: las personas no lo ven.
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}
	$inicio = (int) $g( 'inicio' );
	if ( $inicio && ( time() * 1000 - $inicio ) < 4000 ) {
		return fc_error( 'Fue demasiado rápido. Espera unos segundos e inténtalo de nuevo.' );
	}
	$ip_hash = 'fc_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x' );
	$envios  = (int) get_transient( $ip_hash );
	if ( $envios >= 5 ) {
		return fc_error( 'Recibimos varias cotizaciones desde tu conexión. Escríbenos por WhatsApp y te ayudamos.', '', 429 );
	}

	/* Validación (mismas reglas que el formulario). */
	$uso = $g( 'uso' );
	if ( ! array_key_exists( $uso, fc_opciones_uso() ) ) {
		return fc_error( 'Elige dónde vas a vender.', 'uso' );
	}
	$kits = $g( 'kits' );
	if ( ! array_key_exists( $kits, fc_opciones_kits() ) ) {
		return fc_error( 'Elige cuántos equipos necesitas.', 'kits' );
	}
	$logo = 'si' === $g( 'logo' ) ? 'si' : 'no'; // Opcional (versiones anteriores del cotizador).
	$nombre = sanitize_text_field( $g( 'nombre' ) );
	if ( mb_strlen( $nombre ) < 2 ) {
		return fc_error( 'Escribe tu nombre.', 'name' );
	}
	$tel = preg_replace( '/\D/', '', $g( 'whatsapp' ) );
	if ( 0 === strpos( $tel, '56' ) ) {
		$tel = substr( $tel, 2 );
	}
	if ( ! preg_match( '/^9\d{8}$/', $tel ) ) {
		return fc_error( 'Escribe un celular de 9 dígitos que parta en 9.', 'phone' );
	}
	$correo = sanitize_email( $g( 'correo' ) );
	if ( ! is_email( $correo ) ) {
		return fc_error( 'Revisa el correo.', 'email' );
	}
	$comuna = sanitize_text_field( $g( 'comuna' ) );
	if ( mb_strlen( $comuna ) < 2 ) {
		return fc_error( 'Escribe tu comuna o ciudad.', 'comuna' );
	}
	$origen      = in_array( $g( 'origen' ), fc_opciones_origen(), true ) ? $g( 'origen' ) : '';
	$comentarios = mb_substr( sanitize_textarea_field( $g( 'comentarios' ) ), 0, 2000 );

	/* Logo (opcional). */
	$archivo = null;
	$files   = $req->get_file_params();
	if ( 'si' === $logo && ! empty( $files['archivo'] ) && UPLOAD_ERR_NO_FILE !== (int) $files['archivo']['error'] ) {
		$archivo = fc_guardar_logo( $files['archivo'] );
		if ( is_wp_error( $archivo ) ) {
			return fc_error( $archivo->get_error_message(), 'logo' );
		}
	}

	/* Crear la cotización. */
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'fc_cotizacion',
			'post_status' => 'publish',
			'post_title'  => $nombre . ' · ' . $kits,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return fc_error( 'No pudimos guardar tu cotización. Inténtalo de nuevo o escríbenos por WhatsApp.', '', 500 );
	}
	$meta = array(
		'uso'         => $uso,
		'kits'        => $kits,
		'logo'        => $logo,
		'comentarios' => $comentarios,
		'nombre'      => $nombre,
		'whatsapp'    => '+56 ' . substr( $tel, 0, 1 ) . ' ' . substr( $tel, 1, 4 ) . ' ' . substr( $tel, 5 ),
		'correo'      => $correo,
		'comuna'      => $comuna,
		'origen'      => $origen,
		'estado'      => 'nueva',
	);
	if ( $archivo ) {
		$meta['logo_archivo'] = $archivo['relativa'];
		$meta['logo_nombre']  = $archivo['nombre'];
	}
	foreach ( $meta as $k => $v ) {
		if ( '' !== $v ) {
			update_post_meta( $post_id, '_fc_' . $k, $v );
		}
	}

	set_transient( $ip_hash, $envios + 1, HOUR_IN_SECONDS );
	fc_enviar_correos_cotizacion( $post_id );

	return new WP_REST_Response(
		array(
			'ok'     => true,
			'numero' => $post_id,
			'fecha'  => wp_date( 'j/n/Y' ),
		),
		201
	);
}

/**
 * Valida y guarda el logo en uploads/fc-cotizaciones/AAAA/MM/ con nombre aleatorio.
 * Acepta PNG, JPG, WEBP y PDF. SVG no, porque puede traer código.
 */
function fc_guardar_logo( $f ) {
	if ( UPLOAD_ERR_OK !== (int) $f['error'] ) {
		return new WP_Error( 'subida', 'No pudimos recibir el archivo. Intenta de nuevo o elige «Sin logo» y lo mandas después por correo.' );
	}
	if ( (int) $f['size'] > FC_LOGO_MAX_BYTES ) {
		return new WP_Error( 'peso', 'El archivo pesa más de 10 MB. Prueba con una versión más liviana.' );
	}
	$permitidos = array(
		'png'          => 'image/png',
		'jpg|jpeg|jpe' => 'image/jpeg',
		'webp'         => 'image/webp',
		'pdf'          => 'application/pdf',
	);
	$check = wp_check_filetype_and_ext( $f['tmp_name'], $f['name'], $permitidos );
	if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
		return new WP_Error( 'tipo', 'Ese formato no sirve. Sube un PNG, JPG o PDF.' );
	}
	$base = fc_carpeta_privada();
	$sub  = gmdate( 'Y/m' );
	$dir  = $base . '/' . $sub;
	if ( ! wp_mkdir_p( $dir ) ) {
		return new WP_Error( 'carpeta', 'No pudimos guardar el archivo.' );
	}
	$nombre_final = wp_generate_password( 20, false ) . '.' . $check['ext'];
	if ( ! @move_uploaded_file( $f['tmp_name'], $dir . '/' . $nombre_final ) ) { // phpcs:ignore
		return new WP_Error( 'mover', 'No pudimos guardar el archivo.' );
	}
	@chmod( $dir . '/' . $nombre_final, 0640 ); // phpcs:ignore
	return array(
		'relativa' => $sub . '/' . $nombre_final,
		'nombre'   => sanitize_file_name( $f['name'] ),
	);
}

/** Ruta absoluta del logo de una cotización, o '' si no tiene. */
function fc_cotizacion_logo_ruta( $post_id ) {
	$rel = fc_campo( $post_id, 'logo_archivo' );
	if ( ! $rel || false !== strpos( $rel, '..' ) ) {
		return '';
	}
	$ruta = fc_carpeta_privada() . '/' . $rel;
	return file_exists( $ruta ) ? $ruta : '';
}
