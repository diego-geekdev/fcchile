<?php
/**
 * Tipos de contenido (CPT) y taxonomías.
 *
 * - fc_cotizacion: solicitudes que llegan desde el cotizador. Privadas, no se crean a mano.
 * - fc_paso, fc_pieza, fc_video, fc_faq: contenidos de la portada que edita First Coffee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'fc_registrar_cpts' );

function fc_registrar_cpts() {

	$menu_padre = 'edit.php?post_type=fc_cotizacion';

	/* Cotizaciones: también es el menú principal «First Coffee» del escritorio. */
	register_post_type(
		'fc_cotizacion',
		array(
			'labels'              => array(
				'name'               => 'Cotizaciones',
				'singular_name'      => 'Cotización',
				'menu_name'          => 'First Coffee',
				'all_items'          => 'Cotizaciones',
				'edit_item'          => 'Ver cotización',
				'view_item'          => 'Ver cotización',
				'search_items'       => 'Buscar cotizaciones',
				'not_found'          => 'Todavía no llegan cotizaciones.',
				'not_found_in_trash' => 'No hay cotizaciones en la papelera.',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 3,
			'menu_icon'           => 'dashicons-coffee',
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'capability_type'     => array( 'fc_cotizacion', 'fc_cotizaciones' ),
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		)
	);

	$base_contenido = array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => $menu_padre,
		'show_in_rest'        => false, // Editor clásico: campos simples, sin bloques.
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'capability_type'     => array( 'fc_contenido', 'fc_contenidos' ),
		'map_meta_cap'        => true,
	);

	register_post_type(
		'fc_paso',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Pasos «Cómo funciona»',
					'singular_name' => 'Paso',
					'menu_name'     => 'Pasos',
					'all_items'     => 'Pasos «Cómo funciona»',
					'add_new'       => 'Añadir paso',
					'add_new_item'  => 'Añadir paso',
					'edit_item'     => 'Editar paso',
					'not_found'     => 'No hay pasos.',
				),
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'fc_pieza',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Piezas del kit',
					'singular_name' => 'Pieza',
					'menu_name'     => 'Piezas del kit',
					'all_items'     => 'Piezas del kit',
					'add_new'       => 'Añadir pieza',
					'add_new_item'  => 'Añadir pieza',
					'edit_item'     => 'Editar pieza',
					'not_found'     => 'No hay piezas.',
				),
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'fc_video',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Videos',
					'singular_name' => 'Video',
					'menu_name'     => 'Videos',
					'all_items'     => 'Videos',
					'add_new'       => 'Añadir video',
					'add_new_item'  => 'Añadir video',
					'edit_item'     => 'Editar video',
					'not_found'     => 'No hay videos.',
				),
				'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'fc_faq',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Preguntas frecuentes',
					'singular_name' => 'Pregunta',
					'menu_name'     => 'Preguntas',
					'all_items'     => 'Preguntas frecuentes',
					'add_new'       => 'Añadir pregunta',
					'add_new_item'  => 'Añadir pregunta',
					'edit_item'     => 'Editar pregunta',
					'not_found'     => 'No hay preguntas.',
				),
				'supports' => array( 'title', 'editor', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'fc_beneficio',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Beneficios',
					'singular_name' => 'Beneficio',
					'menu_name'     => 'Beneficios',
					'all_items'     => 'Beneficios («Por qué»)',
					'add_new'       => 'Añadir beneficio',
					'add_new_item'  => 'Añadir beneficio',
					'edit_item'     => 'Editar beneficio',
					'not_found'     => 'No hay beneficios.',
				),
				'supports' => array( 'title', 'editor', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'fc_uso',
		array_merge(
			$base_contenido,
			array(
				'labels'   => array(
					'name'          => 'Usos («Dónde se usa»)',
					'singular_name' => 'Uso',
					'menu_name'     => 'Usos',
					'all_items'     => 'Usos («Dónde se usa»)',
					'add_new'       => 'Añadir uso',
					'add_new_item'  => 'Añadir uso',
					'edit_item'     => 'Editar uso',
					'not_found'     => 'No hay usos.',
				),
				'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		)
	);

	register_taxonomy(
		'fc_tipo_video',
		'fc_video',
		array(
			'labels'            => array(
				'name'          => 'Tipos de video',
				'singular_name' => 'Tipo de video',
				'menu_name'     => 'Tipos de video',
				'add_new_item'  => 'Añadir tipo',
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'hierarchical'      => true, // Se elige con casillas.
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms' => 'edit_fc_contenidos',
				'edit_terms'   => 'edit_fc_contenidos',
				'delete_terms' => 'edit_fc_contenidos',
				'assign_terms' => 'edit_fc_contenidos',
			),
		)
	);
}

/* Textos de ayuda bajo el título. */
add_filter(
	'enter_title_here',
	function ( $texto, $post ) {
		$mapa = array(
			'fc_paso'  => 'Título del paso (por ejemplo: «Hay taco»)',
			'fc_pieza' => 'Nombre de la pieza (por ejemplo: «Mochila térmica»)',
			'fc_video' => 'Texto que aparece sobre el video',
			'fc_faq'   => 'Escribe la pregunta',
			'fc_beneficio' => 'Título corto del beneficio (por ejemplo: «Sin electricidad»)',
			'fc_uso'   => 'Nombre del uso (por ejemplo: «Café en la calle»)',
		);
		return isset( $mapa[ $post->post_type ] ) ? $mapa[ $post->post_type ] : $texto;
	},
	10,
	2
);

/* Mensajes de guardado en español sin enlaces a páginas públicas que no existen. */
add_filter(
	'post_updated_messages',
	function ( $m ) {
		foreach ( array( 'fc_paso', 'fc_pieza', 'fc_video', 'fc_faq', 'fc_beneficio', 'fc_uso', 'fc_cotizacion' ) as $t ) {
			$m[ $t ] = array_fill( 0, 11, 'Cambios guardados.' );
			$m[ $t ][0] = '';
			$m[ $t ][6] = 'Publicado. Ya se ve en el sitio.';
			$m[ $t ][8] = 'Guardado como pendiente.';
			$m[ $t ][10] = 'Borrador guardado.';
		}
		return $m;
	}
);

/* En la lista de contenidos, ordenar por «Orden» por defecto. */
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() ) {
			return;
		}
		$t = $q->get( 'post_type' );
		if ( in_array( $t, array( 'fc_paso', 'fc_pieza', 'fc_video', 'fc_faq', 'fc_beneficio', 'fc_uso' ), true ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order' );
			$q->set( 'order', 'ASC' );
		}
	}
);

/* Columna «Orden» en las listas de contenidos. */
foreach ( array( 'fc_paso', 'fc_pieza', 'fc_video', 'fc_faq', 'fc_beneficio', 'fc_uso' ) as $fc_tipo ) {
	add_filter(
		"manage_{$fc_tipo}_posts_columns",
		function ( $cols ) {
			$nuevo = array();
			foreach ( $cols as $k => $v ) {
				$nuevo[ $k ] = $v;
				if ( 'title' === $k ) {
					$nuevo['fc_orden'] = 'Orden';
				}
			}
			unset( $nuevo['date'] );
			return $nuevo;
		}
	);
	add_action(
		"manage_{$fc_tipo}_posts_custom_column",
		function ( $col, $post_id ) {
			if ( 'fc_orden' === $col ) {
				echo (int) get_post_field( 'menu_order', $post_id );
			}
		},
		10,
		2
	);
}
