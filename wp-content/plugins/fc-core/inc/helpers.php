<?php
/**
 * Funciones de apoyo que también usa el tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Opciones de cantidad de kits del cotizador (valor => [título, bajada]). */
function fc_opciones_kits() {
	return array(
		'1 kit'      => array( '1 kit', 'Para empezar tú' ),
		'2 a 5 kits' => array( '2 a 5 kits', 'Un equipo chico' ),
		'6 o más'    => array( '6 o más', 'Varias esquinas o ciudades' ),
		'Aún no sé'  => array( 'Aún no sé', 'Quiero conversarlo' ),
	);
}

/** Opciones de «¿Cómo nos conociste?». */
function fc_opciones_origen() {
	return array( 'TikTok', 'Instagram', 'Prensa', 'En la calle', 'Otro' );
}

/** Estados de una cotización (clave => etiqueta). */
function fc_estados_cotizacion() {
	return array(
		'nueva'      => 'Nueva',
		'respondida' => 'Respondida',
		'cerrada'    => 'Cerrada',
	);
}

/** Lugares del kit donde se dibuja cada pieza en la portada. */
function fc_slots_kit() {
	return array(
		'mochila' => 'Mochila (pieza central)',
		'termo'   => 'Termo (arriba a la derecha)',
		'piezas'  => 'Accesorios (abajo a la derecha)',
	);
}

/**
 * Lee un ajuste del sitio (página «First Coffee → Ajustes»).
 * Si está vacío devuelve el valor por defecto.
 */
function fc_opt( $clave, $defecto = '' ) {
	$todo = get_option( 'fc_ajustes', array() );
	if ( isset( $todo[ $clave ] ) && '' !== trim( (string) $todo[ $clave ] ) ) {
		return $todo[ $clave ];
	}
	$def = fc_ajustes_defecto();
	if ( '' === $defecto && isset( $def[ $clave ] ) ) {
		return $def[ $clave ];
	}
	return $defecto;
}

/** Número de WhatsApp solo con dígitos y código de país (569XXXXXXXX). */
function fc_whatsapp_numero() {
	$d = preg_replace( '/\D/', '', (string) fc_opt( 'whatsapp' ) );
	if ( '' === $d ) {
		return '';
	}
	if ( 9 === strlen( $d ) ) {
		$d = '56' . $d;
	}
	return $d;
}

/** Enlace wa.me con mensaje opcional. */
function fc_whatsapp_url( $mensaje = '' ) {
	$n = fc_whatsapp_numero();
	if ( '' === $n ) {
		return '';
	}
	$url = 'https://wa.me/' . $n;
	if ( '' !== $mensaje ) {
		$url .= '?text=' . rawurlencode( $mensaje );
	}
	return $url;
}

/**
 * Devuelve los elementos publicados de un CPT de contenido, ordenados por «Orden».
 *
 * @param string $tipo  fc_paso | fc_pieza | fc_video | fc_faq.
 * @param int    $max   Máximo de elementos (-1 = todos).
 * @return WP_Post[]
 */
function fc_items( $tipo, $max = -1 ) {
	return get_posts(
		array(
			'post_type'        => $tipo,
			'post_status'      => 'publish',
			'posts_per_page'   => $max,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'suppress_filters' => false,
		)
	);
}

/** Lee un campo propio de un post (los campos se guardan con prefijo _fc_). */
function fc_campo( $post_id, $clave, $defecto = '' ) {
	$v = get_post_meta( $post_id, '_fc_' . $clave, true );
	return ( '' === $v || null === $v ) ? $defecto : $v;
}

/** Ruta de la carpeta privada donde quedan los logos de las cotizaciones. */
function fc_carpeta_privada() {
	$up  = wp_upload_dir( null, false );
	$dir = trailingslashit( $up['basedir'] ) . 'fc-cotizaciones';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	// Bloquea el acceso directo desde la web (Apache / LiteSpeed). Los logos se descargan desde el escritorio.
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		@file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore
	}
	if ( ! file_exists( $dir . '/index.php' ) ) {
		@file_put_contents( $dir . '/index.php', "<?php // Silencio.\n" ); // phpcs:ignore
	}
	return $dir;
}
