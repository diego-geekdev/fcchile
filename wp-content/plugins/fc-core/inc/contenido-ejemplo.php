<?php
/**
 * Botón «Cargar contenido de ejemplo» (Ajustes): crea los textos del prototipo
 * solo en los tipos que estén vacíos. Los datos entre corchetes los completa First Coffee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_post_fc_contenido_ejemplo',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'fc_contenido_ejemplo' ) ) {
			wp_die( 'Sin permiso.', 403 );
		}
		$creados = fc_cargar_contenido_ejemplo();
		wp_safe_redirect( admin_url( 'edit.php?post_type=fc_cotizacion&page=fc-ajustes&fc_ejemplo=' . (int) $creados ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( isset( $_GET['fc_ejemplo'] ) ) { // phpcs:ignore
			$n = (int) $_GET['fc_ejemplo']; // phpcs:ignore
			$t = $n ? sprintf( 'Listo: se crearon %d contenidos de ejemplo. Revísalos en el menú First Coffee.', $n ) : 'No se creó nada: ya había contenido en todas las secciones.';
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $t ) );
		}
	}
);

function fc_cargar_contenido_ejemplo() {
	$n = 0;
	$hay = function ( $tipo ) {
		return (bool) get_posts(
			array(
				'post_type'      => $tipo,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
	};
	$crear = function ( $tipo, $titulo, $contenido, $orden, $meta = array() ) use ( &$n ) {
		$id = wp_insert_post(
			array(
				'post_type'    => $tipo,
				'post_status'  => 'publish',
				'post_title'   => $titulo,
				'post_content' => $contenido,
				'menu_order'   => $orden,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			foreach ( $meta as $k => $v ) {
				update_post_meta( $id, '_fc_' . $k, $v );
			}
			$n++;
		}
		return $id;
	};

	if ( ! $hay( 'fc_paso' ) ) {
		$pasos = array(
			array( 'Hay taco', 'Avenida detenida, gente apurada y sin desayuno. Ahí partimos, en las esquinas con más autos de Las Condes.' ),
			array( 'Llega a tu ventanilla', 'Un barista camina entre los autos con la mochila y el termo. Bajas el vidrio y pides.' ),
			array( 'Pagas al tiro', 'Pagas sin bajarte del auto. Medios de pago: [confirmar con First Coffee].' ),
			array( 'Sigues con café', 'Café de grano en la mano antes de que cambie la luz. El barista ya va al auto de atrás.' ),
		);
		foreach ( $pasos as $i => $p ) {
			$crear( 'fc_paso', $p[0], $p[1], $i + 1, array( 'etiqueta' => 'Clip 3 s' ) );
		}
	}

	if ( ! $hay( 'fc_pieza' ) ) {
		$crear( 'fc_pieza', 'Mochila térmica', 'Lleva el termo y los vasos en la espalda, con las manos libres para atender en la ventanilla.', 1, array( 'slot' => 'mochila', 'etiqueta' => 'Mochila térmica', 'specs' => "Capacidad: [por confirmar]\nMaterial: [por confirmar]\nSe puede personalizar con tu logo" ) );
		$crear( 'fc_pieza', 'Termo', 'Mantiene el café caliente durante el turno y sirve directo desde la llave.', 2, array( 'slot' => 'termo', 'etiqueta' => 'Termo [litros]', 'specs' => "Capacidad: [litros]\nMantiene la temperatura: [horas]\nLlave dispensadora" ) );
		$crear( 'fc_pieza', 'Piezas y accesorios', 'Vasos, tapas y las piezas que arma nuestro mecánico para que todo encaje.', 3, array( 'slot' => 'piezas', 'etiqueta' => 'Vasos, tapas y piezas', 'specs' => "Incluye: [lista de piezas]\nRepuestos: a la venta en la Fase 2" ) );
	}

	if ( ! $hay( 'fc_video' ) ) {
		$tipos = array();
		foreach ( array( 'Viral de TikTok', 'Prensa', 'Reel' ) as $t ) {
			$term        = term_exists( $t, 'fc_tipo_video' );
			$term        = $term ? $term : wp_insert_term( $t, 'fc_tipo_video' );
			$tipos[ $t ] = is_array( $term ) ? (int) $term['term_id'] : 0;
		}
		$videos = array(
			array( '¡Café en pleno taco!', 'Viral de TikTok' ),
			array( 'El emprendimiento que la está rompiendo', 'Prensa' ),
			array( 'A 2 luquitas', 'Reel' ),
			array( 'Hoy lo encuentro lo máximo', 'Reel' ),
			array( 'Un día en la calle', 'Reel' ),
		);
		foreach ( $videos as $i => $v ) {
			$id = $crear( 'fc_video', $v[0], '', $i + 1, array( 'url' => 'https://www.instagram.com/firstcoffeechile/' ) );
			if ( $id && $tipos[ $v[1] ] ) {
				wp_set_object_terms( $id, array( $tipos[ $v[1] ] ), 'fc_tipo_video' );
			}
		}
	}

	if ( ! $hay( 'fc_faq' ) ) {
		$faq = array(
			array( '¿Qué incluye el kit?', 'La mochila térmica, el termo con llave dispensadora y las piezas que arma nuestro mecánico para que todo encaje. Detalle de piezas: [lista por confirmar].' ),
			array( '¿Cuánto cuesta el kit?', '[Precio o rango por confirmar]. Depende de la cantidad y la personalización; por eso lo cotizamos según lo que necesitas.' ),
			array( '¿Le pueden poner el logo de mi marca?', 'Sí. Puedes subirlo en el cotizador y ver cómo queda en la mochila antes de enviar tu solicitud.' ),
			array( '¿Cuánto demoran en armarlo?', '[Plazo de fabricación por confirmar].' ),
			array( '¿Hacen envíos fuera de Santiago?', '[Respuesta por confirmar].' ),
			array( '¿El kit incluye café?', 'Por ahora el kit es el equipo. Más adelante vamos a sumar marcas de café a la tienda.' ),
		);
		foreach ( $faq as $i => $f ) {
			$crear( 'fc_faq', $f[0], $f[1], $i + 1 );
		}
	}

	/* Cifras del prototipo, solo si están vacías. */
	$aj = get_option( 'fc_ajustes', array() );
	if ( empty( $aj['stat1_valor'] ) && empty( $aj['stat2_valor'] ) ) {
		$aj['stat1_valor'] = '6.800+';
		$aj['stat1_texto'] = 'seguidores en Instagram';
		$aj['stat2_valor'] = '$2.000';
		$aj['stat2_texto'] = 'el café de grano';
		update_option( 'fc_ajustes', $aj );
	}

	return $n;
}
