<?php
/**
 * Tema First Coffee: carga de estilos y scripts, soporte del tema y datos para el JavaScript.
 * Los contenidos y el cotizador viven en el plugin fc-core; aquí solo se presentan.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FCT_VERSION', '2.0.0' );

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'script', 'style', 'gallery', 'caption' ) );
		add_image_size( 'fc-vertical', 720, 1100, true );
		add_image_size( 'fc-panel', 1100, 620, true );
		add_post_type_support( 'page', 'excerpt' );
		register_nav_menus( array( 'principal' => 'Menú principal' ) );
	}
);

/**
 * Enlaces del menú principal: el menú «Menú principal» si está asignado en Apariencia → Menús;
 * si no, las páginas del sitio por slug (las que existan).
 *
 * @return array[] [ [url, texto, activo] ]
 */
function fct_menu() {
	$out  = array();
	$locs = get_nav_menu_locations();
	if ( ! empty( $locs['principal'] ) ) {
		$items = wp_get_nav_menu_items( $locs['principal'] );
		foreach ( (array) $items as $it ) {
			if ( ! $it->menu_item_parent ) {
				$out[] = array( $it->url, $it->title, (int) $it->object_id === get_queried_object_id() && 'post_type' === $it->type );
			}
		}
		return $out;
	}
	$out[] = array( home_url( '/' ), 'Máquinas', is_front_page() );
	$paginas = array(
		'la-empresa' => 'La empresa',
		'ficha'      => 'Ficha y rentabilidad',
		'manual'     => 'Manual',
		'servicio'   => 'Servicio',
		'brochures'  => 'Brochures',
	);
	foreach ( $paginas as $slug => $texto ) {
		$pg = get_page_by_path( $slug );
		if ( $pg && 'publish' === $pg->post_status ) {
			$activo = is_page( $pg->ID ) || ( is_page() && in_array( $pg->ID, get_post_ancestors( get_queried_object_id() ), true ) );
			$out[]  = array( get_permalink( $pg ), $texto, $activo );
		}
	}
	return $out;
}

/** URL de una página por slug (o el inicio si no existe). */
function fct_pagina_url( $slug, $ancla = '' ) {
	$pg = get_page_by_path( $slug );
	return ( $pg ? get_permalink( $pg ) : home_url( '/' ) ) . $ancla;
}

/** Ícono SVG de un beneficio. */
function fct_icono( $clave ) {
	$i = array(
		'calor'    => '<path d="M5 11h11v4a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5v-4Z"/><path d="M16 12h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M8 3c-.8 1 .8 2 0 3M11 3c-.8 1 .8 2 0 3M14 3c-.8 1 .8 2 0 3"/>',
		'manos'    => '<rect x="6" y="6" width="12" height="15" rx="3"/><path d="M9 6V4.5a3 3 0 0 1 6 0V6M9 13h6"/>',
		'energia'  => '<path d="M13 2 5 13h6l-1 9 8-11h-6l1-9Z"/><path d="M3 3l18 18"/>',
		'limpieza' => '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"/><path d="M9.5 15a2.5 2.5 0 0 0 2.5 2.5"/>',
		'chile'    => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1"/>',
		'listo'    => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.8 2.8L16.5 9.5"/>',
	);
	$d = isset( $i[ $clave ] ) ? $i[ $clave ] : $i['listo'];
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

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
