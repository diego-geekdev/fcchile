<?php
/**
 * Panel de cotizaciones en el escritorio: columnas, filtro por estado, cambio de estado,
 * notas internas, descarga del logo y aviso de cotizaciones nuevas en el menú.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Lista ---------- */

add_filter(
	'manage_fc_cotizacion_posts_columns',
	function () {
		return array(
			'cb'        => '<input type="checkbox">',
			'fc_fecha'  => 'Fecha',
			'title'     => 'Nombre',
			'fc_uso'    => 'Dónde venderá',
			'fc_kits'   => 'Equipos',
			'fc_contac' => 'Contacto',
			'fc_comuna' => 'Comuna',
			'fc_estado' => 'Estado',
		);
	}
);

add_action(
	'manage_fc_cotizacion_posts_custom_column',
	function ( $col, $id ) {
		switch ( $col ) {
			case 'fc_fecha':
				echo esc_html( get_the_date( 'j M Y · H:i', $id ) );
				break;
			case 'fc_uso':
				echo esc_html( fc_campo( $id, 'uso', '—' ) );
				break;
			case 'fc_kits':
				echo esc_html( fc_campo( $id, 'kits' ) );
				break;
			case 'fc_logo':
				if ( 'si' !== fc_campo( $id, 'logo' ) ) {
					echo 'No';
				} elseif ( fc_cotizacion_logo_ruta( $id ) ) {
					printf( '<a href="%s">Descargar</a>', esc_url( fc_url_logo( $id ) ) );
				} else {
					echo 'Sí, lo enviará';
				}
				break;
			case 'fc_contac':
				$wa = preg_replace( '/\D/', '', fc_campo( $id, 'whatsapp' ) );
				printf(
					'<a href="https://wa.me/%1$s" target="_blank" rel="noopener">%2$s</a><br><a href="mailto:%3$s">%3$s</a>',
					esc_attr( $wa ),
					esc_html( fc_campo( $id, 'whatsapp' ) ),
					esc_html( fc_campo( $id, 'correo' ) )
				);
				break;
			case 'fc_comuna':
				echo esc_html( fc_campo( $id, 'comuna' ) );
				break;
			case 'fc_estado':
				echo fc_pill_estado( fc_campo( $id, 'estado', 'nueva' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				break;
		}
	},
	10,
	2
);

add_filter(
	'manage_edit-fc_cotizacion_sortable_columns',
	function ( $c ) {
		$c['fc_fecha'] = 'date';
		return $c;
	}
);

function fc_pill_estado( $estado ) {
	$estados = fc_estados_cotizacion();
	$colores = array(
		'nueva'      => 'background:#C99A27;color:#080808',
		'respondida' => 'background:#69452F;color:#fff',
		'cerrada'    => 'background:#e2e2e2;color:#333',
	);
	$e = isset( $estados[ $estado ] ) ? $estado : 'nueva';
	return sprintf( '<span style="display:inline-block;padding:3px 10px;border-radius:99px;font-weight:600;font-size:12px;%s">%s</span>', esc_attr( $colores[ $e ] ), esc_html( $estados[ $e ] ) );
}

/* Quitar acciones que no aplican (ver, edición rápida). */
add_filter(
	'post_row_actions',
	function ( $acciones, $post ) {
		if ( 'fc_cotizacion' === $post->post_type ) {
			unset( $acciones['view'], $acciones['inline hide-if-no-js'] );
			if ( isset( $acciones['edit'] ) ) {
				$acciones['edit'] = sprintf( '<a href="%s">Ver detalle</a>', esc_url( get_edit_post_link( $post->ID ) ) );
			}
		}
		return $acciones;
	},
	10,
	2
);

/* Filtro por estado. */
add_action(
	'restrict_manage_posts',
	function ( $tipo ) {
		if ( 'fc_cotizacion' !== $tipo ) {
			return;
		}
		$actual = isset( $_GET['fc_estado'] ) ? sanitize_key( $_GET['fc_estado'] ) : ''; // phpcs:ignore
		echo '<select name="fc_estado"><option value="">Todos los estados</option>';
		foreach ( fc_estados_cotizacion() as $k => $v ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $actual, $k, false ), esc_html( $v ) );
		}
		echo '</select>';
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'fc_cotizacion' !== $q->get( 'post_type' ) ) {
			return;
		}
		$e = isset( $_GET['fc_estado'] ) ? sanitize_key( $_GET['fc_estado'] ) : ''; // phpcs:ignore
		if ( $e && array_key_exists( $e, fc_estados_cotizacion() ) ) {
			$q->set(
				'meta_query',
				array(
					array(
						'key'   => '_fc_estado',
						'value' => $e,
					),
				)
			);
		}
		if ( ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
		}
	}
);

/* Búsqueda también por correo, teléfono y comuna. */
add_filter(
	'posts_search',
	function ( $search, $q ) {
		global $wpdb;
		if ( ! is_admin() || ! $q->is_main_query() || 'fc_cotizacion' !== $q->get( 'post_type' ) || ! $q->get( 's' ) ) {
			return $search;
		}
		$like = '%' . $wpdb->esc_like( $q->get( 's' ) ) . '%';
		return $wpdb->prepare(
			" AND ( {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_fc_correo','_fc_whatsapp','_fc_comuna') AND meta_value LIKE %s ) ) ",
			$like,
			$like
		);
	},
	10,
	2
);

/* Acciones masivas: cambiar estado. */
add_filter(
	'bulk_actions-edit-fc_cotizacion',
	function ( $a ) {
		unset( $a['edit'] );
		foreach ( fc_estados_cotizacion() as $k => $v ) {
			$a[ 'fc_estado_' . $k ] = 'Marcar como «' . $v . '»';
		}
		return $a;
	}
);

add_filter(
	'handle_bulk_actions-edit-fc_cotizacion',
	function ( $redir, $accion, $ids ) {
		if ( 0 !== strpos( $accion, 'fc_estado_' ) ) {
			return $redir;
		}
		$e = substr( $accion, 10 );
		if ( ! array_key_exists( $e, fc_estados_cotizacion() ) ) {
			return $redir;
		}
		$n = 0;
		foreach ( $ids as $id ) {
			if ( current_user_can( 'edit_post', $id ) ) {
				update_post_meta( $id, '_fc_estado', $e );
				$n++;
			}
		}
		return add_query_arg( 'fc_cambiadas', $n, $redir );
	},
	10,
	3
);

add_action(
	'admin_notices',
	function () {
		if ( ! empty( $_GET['fc_cambiadas'] ) ) { // phpcs:ignore
			printf( '<div class="notice notice-success is-dismissible"><p>Estado actualizado en %d cotización(es).</p></div>', (int) $_GET['fc_cambiadas'] ); // phpcs:ignore
		}
	}
);

/* Burbuja con el número de cotizaciones nuevas en el menú. */
add_action(
	'admin_menu',
	function () {
		global $menu;
		if ( ! current_user_can( 'edit_fc_cotizaciones' ) ) {
			return;
		}
		$n = (int) get_transient( 'fc_nuevas_count' );
		if ( false === get_transient( 'fc_nuevas_count' ) ) {
			$q = new WP_Query(
				array(
					'post_type'      => 'fc_cotizacion',
					'post_status'    => 'publish',
					'meta_key'       => '_fc_estado', // phpcs:ignore
					'meta_value'     => 'nueva', // phpcs:ignore
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'no_found_rows'  => false,
				)
			);
			$n = (int) $q->found_posts;
			set_transient( 'fc_nuevas_count', $n, 5 * MINUTE_IN_SECONDS );
		}
		if ( ! $n ) {
			return;
		}
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=fc_cotizacion' === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>'; // phpcs:ignore
			}
		}
	},
	99
);

add_action(
	'updated_post_meta',
	function ( $mid, $pid, $key ) {
		if ( '_fc_estado' === $key ) {
			delete_transient( 'fc_nuevas_count' );
		}
	},
	10,
	3
);
add_action(
	'added_post_meta',
	function ( $mid, $pid, $key ) {
		if ( '_fc_estado' === $key ) {
			delete_transient( 'fc_nuevas_count' );
		}
	},
	10,
	3
);
add_action(
	'deleted_post',
	function () {
		delete_transient( 'fc_nuevas_count' );
	}
);

/* ---------- Detalle de una cotización ---------- */

add_action(
	'add_meta_boxes_fc_cotizacion',
	function () {
		add_meta_box( 'fc_cot_detalle', 'Detalle de la cotización', 'fc_cot_detalle_render', 'fc_cotizacion', 'normal', 'high' );
		add_meta_box( 'fc_cot_estado', 'Estado y notas internas', 'fc_cot_estado_render', 'fc_cotizacion', 'side', 'high' );
	}
);

function fc_cot_detalle_render( $post ) {
	echo '<table class="widefat striped" style="border:0"><tbody>';
	printf( '<tr><th style="width:170px">Recibida</th><td>%s</td></tr>', esc_html( get_the_date( 'l j \d\e F \d\e Y, H:i', $post ) ) );
	foreach ( fc_cotizacion_resumen( $post->ID ) as $k => $v ) {
		if ( 'WhatsApp' === $k ) {
			$wa = preg_replace( '/\D/', '', $v );
			$v  = sprintf( '%s · <a href="https://wa.me/%s" target="_blank" rel="noopener">Abrir WhatsApp</a>', esc_html( $v ), esc_attr( $wa ) );
			printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $k ), $v ); // phpcs:ignore
			continue;
		}
		if ( 'Correo' === $k ) {
			printf( '<tr><th>%1$s</th><td><a href="mailto:%2$s">%2$s</a></td></tr>', esc_html( $k ), esc_html( $v ) );
			continue;
		}
		if ( 'Logo' === $k && fc_cotizacion_logo_ruta( $post->ID ) ) {
			$v = esc_html( $v ) . sprintf( ' · <a class="button button-small" href="%s">Descargar logo</a>', esc_url( fc_url_logo( $post->ID ) ) );
			printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $k ), $v ); // phpcs:ignore
			continue;
		}
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $k ), nl2br( esc_html( $v ) ) );
	}
	$aviso   = fc_campo( $post->ID, 'correo_aviso', '—' );
	$cliente = fc_campo( $post->ID, 'correo_cliente', '—' );
	printf( '<tr><th>Correos</th><td>Aviso interno: %s · Copia al cliente: %s</td></tr>', esc_html( $aviso ), esc_html( $cliente ) );
	echo '</tbody></table>';
}

function fc_cot_estado_render( $post ) {
	wp_nonce_field( 'fc_cot_estado', 'fc_cot_estado_nonce' );
	$actual = fc_campo( $post->ID, 'estado', 'nueva' );
	echo '<p><label for="fc_estado_sel"><strong>Estado</strong></label><br><select id="fc_estado_sel" name="fc_estado_sel" style="width:100%">';
	foreach ( fc_estados_cotizacion() as $k => $v ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $actual, $k, false ), esc_html( $v ) );
	}
	echo '</select></p>';
	printf(
		'<p><label for="fc_notas"><strong>Notas internas</strong></label><br><textarea id="fc_notas" name="fc_notas" rows="5" style="width:100%%" placeholder="Precio ofrecido, fecha de entrega, seguimiento…">%s</textarea></p><p class="description">El cliente no ve estas notas. Guarda con el botón «Actualizar».</p>',
		esc_textarea( fc_campo( $post->ID, 'notas' ) )
	);
}

add_action(
	'save_post_fc_cotizacion',
	function ( $post_id ) {
		if ( ! isset( $_POST['fc_cot_estado_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fc_cot_estado_nonce'] ) ), 'fc_cot_estado' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$e = isset( $_POST['fc_estado_sel'] ) ? sanitize_key( $_POST['fc_estado_sel'] ) : 'nueva';
		if ( array_key_exists( $e, fc_estados_cotizacion() ) ) {
			update_post_meta( $post_id, '_fc_estado', $e );
		}
		update_post_meta( $post_id, '_fc_notas', isset( $_POST['fc_notas'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fc_notas'] ) ) : '' );
	}
);

/* La cotización no tiene contenido editable: ocultamos el editor de título y el enlace «ver». */
add_action(
	'admin_head',
	function () {
		$s = get_current_screen();
		if ( $s && 'fc_cotizacion' === $s->post_type && 'post' === $s->base ) {
			echo '<style>#titlediv #title{background:#f6f7f7}#edit-slug-box,#minor-publishing-actions,#misc-publishing-actions .misc-pub-visibility,#misc-publishing-actions .misc-pub-post-status{display:none}</style>';
		}
	}
);

/* ---------- Descarga protegida del logo ---------- */

function fc_url_logo( $post_id ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=fc_logo&id=' . (int) $post_id ), 'fc_logo_' . (int) $post_id );
}

add_action(
	'admin_post_fc_logo',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! $id || ! check_admin_referer( 'fc_logo_' . $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'No tienes permiso para descargar este archivo.', 403 );
		}
		$ruta = fc_cotizacion_logo_ruta( $id );
		if ( ! $ruta ) {
			wp_die( 'El archivo ya no existe.', 404 );
		}
		$tipo   = wp_check_filetype( $ruta );
		$nombre = fc_campo( $id, 'logo_nombre', basename( $ruta ) );
		nocache_headers();
		header( 'Content-Type: ' . ( $tipo['type'] ? $tipo['type'] : 'application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . str_replace( '"', '', $nombre ) . '"' );
		header( 'Content-Length: ' . filesize( $ruta ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $ruta ); // phpcs:ignore
		exit;
	}
);

/* Al borrar una cotización definitivamente, se borra también su logo. */
add_action(
	'before_delete_post',
	function ( $post_id ) {
		if ( 'fc_cotizacion' !== get_post_type( $post_id ) ) {
			return;
		}
		$ruta = fc_cotizacion_logo_ruta( $post_id );
		if ( $ruta ) {
			wp_delete_file( $ruta );
		}
	}
);
