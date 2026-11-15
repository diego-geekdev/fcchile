<?php
/**
 * Tema First Coffee: carga de estilos y scripts, soporte del tema y datos para el JavaScript.
 * Los contenidos y el cotizador viven en el plugin fc-core; aquí solo se presentan.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FCT_VERSION', '1.1.0' );

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'script', 'style', 'gallery', 'caption' ) );
		add_image_size( 'fc-vertical', 720, 1100, true );
		add_image_size( 'fc-panel', 1100, 620, true );
	}
);

/** Ajuste del sitio con respaldo si el plugin no está activo. */
function fct_opt( $clave, $defecto = '' ) {
	return function_exists( 'fc_opt' ) ? fc_opt( $clave, $defecto ) : $defecto;
}

/** Contenidos de un CPT (vacío si el plugin no está activo). */
function fct_items( $tipo, $max = -1 ) {
	return function_exists( 'fc_items' ) ? fc_items( $tipo, $max ) : array();
}

/** Campo propio de un contenido. */
function fct_campo( $id, $clave, $defecto = '' ) {
	return function_exists( 'fc_campo' ) ? fc_campo( $id, $clave, $defecto ) : $defecto;
}

/** URL de una imagen del tema. */
function fct_img( $archivo ) {
	return get_template_directory_uri() . '/assets/img/' . $archivo;
}

/** Enlace de WhatsApp (o al cotizador si no hay número). */
function fct_whatsapp( $mensaje = 'Hola, quiero cotizar un kit' ) {
	$url = function_exists( 'fc_whatsapp_url' ) ? fc_whatsapp_url( $mensaje ) : '';
	return $url ? $url : '#cotizar';
}

/** Piezas del kit indexadas por lugar en la ilustración (mochila, termo, piezas). */
function fct_piezas() {
	$out = array();
	foreach ( fct_items( 'fc_pieza' ) as $p ) {
		$slot = fct_campo( $p->ID, 'slot', 'mochila' );
		if ( isset( $out[ $slot ] ) ) {
			continue; // Un lugar, una pieza.
		}
		$specs        = array_values( array_filter( array_map( 'trim', explode( "\n", (string) fct_campo( $p->ID, 'specs' ) ) ) ) );
		$out[ $slot ] = array(
			't'     => get_the_title( $p ),
			'd'     => wp_strip_all_tags( $p->post_content ),
			's'     => $specs,
			'label' => fct_campo( $p->ID, 'etiqueta', get_the_title( $p ) ),
		);
	}
	return $out;
}

/** Cifra animable: devuelve [número, formato] o null si no es una cifra simple. */
function fct_cifra( $valor ) {
	$v = trim( (string) $valor );
	if ( preg_match( '/^(\$)?\s?([\d\.]+)(\+)?$/', $v, $m ) ) {
		$num = (int) str_replace( '.', '', $m[2] );
		$fmt = $m[1] ? 'money' : ( ! empty( $m[3] ) ? 'plus' : '' );
		return array( $num, $fmt );
	}
	return null;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		wp_enqueue_style( 'fc-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400..900&display=swap', array(), null );
		wp_enqueue_style( 'fc-main', $uri . '/assets/css/main.css', array( 'fc-inter' ), filemtime( $dir . '/assets/css/main.css' ) );

		if ( ! is_front_page() ) {
			return; // Las demás páginas usan solo los estilos.
		}

		wp_enqueue_script( 'lenis', $uri . '/assets/js/lenis.min.js', array(), '1.1.13', true );
		wp_enqueue_script( 'fc-main', $uri . '/assets/js/main.js', array( 'lenis' ), filemtime( $dir . '/assets/js/main.js' ), true );

		$datos = array(
			'piezas'   => (object) fct_piezas(),
			'cotizar'  => function_exists( 'fc_cotizador_datos_js' ) ? fc_cotizador_datos_js() : null,
			'whatsapp' => fct_whatsapp(),
		);
		wp_add_inline_script( 'fc-main', 'window.fcData=' . wp_json_encode( $datos ) . ';', 'before' );
	}
);

/* Preconexión a Google Fonts. */
add_filter(
	'wp_resource_hints',
	function ( $urls, $tipo ) {
		if ( 'preconnect' === $tipo ) {
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	},
	10,
	2
);

/* Menos peso: sin emojis de WordPress. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* Favicon con el isotipo si no se configuró un ícono del sitio. */
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" type="image/png" href="%s">' . "\n", esc_url( fct_img( 'first-coffee-isotipo-negro.png' ) ) );
		}
		echo '<meta name="theme-color" content="#080808">' . "\n";
	},
	2
);

/* Aviso si falta el plugin. */
add_action(
	'admin_notices',
	function () {
		if ( ! function_exists( 'fc_opt' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-warning"><p><strong>Tema First Coffee:</strong> activa el plugin «First Coffee — Núcleo del sitio» para ver los contenidos y el cotizador.</p></div>';
		}
	}
);
