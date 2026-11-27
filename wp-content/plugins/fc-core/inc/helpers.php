<?php
/**
 * Funciones de apoyo que también usa el tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Cantidad de equipos del cotizador (valor => [título, bajada]). */
function fc_opciones_kits() {
	return array(
		'1 equipo'       => array( '1 equipo', 'Para partir yo' ),
		'2 a 5 equipos'  => array( '2 a 5 equipos', 'Un equipo de venta' ),
		'6 o más'        => array( '6 o más', 'Varias esquinas o sucursales' ),
		'Aún no sé'      => array( 'Aún no sé', 'Quiero conversarlo' ),
	);
}

/** ¿Dónde vas a vender? (valor => [título, bajada]). */
function fc_opciones_uso() {
	return array(
		'Calle'            => array( 'En la calle', 'Paraderos, metro, oficinas, ferias' ),
		'Eventos y ferias' => array( 'Eventos y ferias', 'Matrimonios, deporte, empresas' ),
		'Oficina o local'  => array( 'Oficina o local', 'Recepción, turnos, obras, clínicas' ),
		'Reparto'          => array( 'Reparto', 'A pie o en bicicleta' ),
	);
}

/** Formato chileno de números: 550000 => 550.000 ; 10.5 => 10,5 */
function fc_num( $n, $dec = 0 ) {
	return number_format( (float) $n, $dec, ',', '.' );
}

/** Lee un ajuste numérico (acepta «550.000», «$2.490», «60%»). */
function fc_opt_num( $clave ) {
	$v = (string) fc_opt( $clave );
	$v = str_replace( array( '$', '%', ' ', '.' ), '', $v );
	$v = str_replace( ',', '.', $v );
	return (float) $v;
}

/**
 * Escenario de recuperación del equipo.
 * @return array venta, margen, dias (jornadas para recuperar), semanas
 */
function fc_escenario( $vasos, $dias_semana = null ) {
	$precio = fc_opt_num( 'precio_vaso' );
	$margen = fc_opt_num( 'margen' ) / 100;
	$equipo = fc_opt_num( 'precio_equipo' );
	$dias_semana = $dias_semana ? $dias_semana : max( 1, fc_opt_num( 'jornadas_semana' ) );
	$venta  = $vasos * $precio;
	$mg     = $venta * $margen;
	$dias   = $mg > 0 ? $equipo / $mg : 0;
	return array(
		'venta'   => $venta,
		'margen'  => $mg,
		'dias'    => $dias,
		'semanas' => $dias / $dias_semana,
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

/** Íconos disponibles para los beneficios. */
function fc_iconos_beneficio() {
	return array(
		'calor'    => 'Café caliente (taza con vapor)',
		'manos'    => 'Manos libres (mochila)',
		'energia'  => 'Sin electricidad (rayo tachado)',
		'limpieza' => 'Limpieza fácil (gota)',
		'chile'    => 'Ingeniería chilena (engranaje)',
		'listo'    => 'Listo para usar (check)',
	);
}

/** Lugares del kit donde se dibuja cada pieza en la portada. */
function fc_slots_kit() {
	return array(
		'mochila' => 'Mochila térmica (pieza central)',
		'termo'   => 'Estanque (arriba a la derecha)',
		'piezas'  => 'Línea de servicio y bomba (abajo a la derecha)',
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
