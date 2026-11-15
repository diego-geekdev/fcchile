<?php
/**
 * Campos propios de los contenidos (meta boxes nativas, sin ACF).
 * Cada campo se guarda como post meta con prefijo «_fc_».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definición de campos por tipo.
 * type: text | textarea | lines | url | select | media
 */
function fc_campos_definicion() {
	return array(
		'fc_paso'  => array(
			'titulo' => 'Datos del paso',
			'ayuda'  => 'El texto del paso va en el editor de arriba. La imagen destacada (columna derecha) reemplaza la ilustración de la calle. El orden se define en «Atributos → Orden».',
			'campos' => array(
				'etiqueta' => array(
					'label' => 'Etiqueta al pasar el mouse',
					'type'  => 'text',
					'ph'    => 'Clip 3 s',
				),
				'video'    => array(
					'label' => 'Clip de video (opcional, MP4 corto)',
					'type'  => 'media',
					'mime'  => 'video',
				),
			),
		),
		'fc_pieza' => array(
			'titulo' => 'Datos de la pieza',
			'ayuda'  => 'La descripción va en el editor de arriba. Hay tres lugares en la ilustración del kit: cada pieza ocupa uno.',
			'campos' => array(
				'slot'     => array(
					'label'   => 'Lugar en la ilustración',
					'type'    => 'select',
					'options' => 'fc_slots_kit',
				),
				'etiqueta' => array(
					'label' => 'Etiqueta corta en la ilustración',
					'type'  => 'text',
					'ph'    => 'Termo 5 litros',
				),
				'specs'    => array(
					'label' => 'Características (una por línea)',
					'type'  => 'lines',
					'ph'    => "Capacidad: 5 litros\nMantiene la temperatura: 6 horas",
				),
			),
		),
		'fc_video' => array(
			'titulo' => 'Datos del video',
			'ayuda'  => 'La imagen destacada se usa como portada del recuadro. Se muestran los primeros 5 videos según el orden. El tipo (Viral, Prensa, Reel) se elige en la caja «Tipos de video».',
			'campos' => array(
				'url'   => array(
					'label' => 'Enlace a la publicación (TikTok o Instagram)',
					'type'  => 'url',
					'ph'    => 'https://www.tiktok.com/@…',
				),
				'video' => array(
					'label' => 'Archivo de video vertical (opcional, MP4)',
					'type'  => 'media',
					'mime'  => 'video',
				),
			),
		),
	);
}

add_action(
	'add_meta_boxes',
	function () {
		foreach ( fc_campos_definicion() as $tipo => $def ) {
			add_meta_box( 'fc_campos', $def['titulo'], 'fc_metabox_render', $tipo, 'normal', 'high' );
		}
	}
);

function fc_metabox_render( $post ) {
	$defs = fc_campos_definicion();
	$def  = $defs[ $post->post_type ];
	wp_nonce_field( 'fc_guardar_campos', 'fc_campos_nonce' );
	echo '<p class="description" style="margin-bottom:12px">' . esc_html( $def['ayuda'] ) . '</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $def['campos'] as $clave => $c ) {
		$id  = 'fc_' . $clave;
		$val = get_post_meta( $post->ID, '_fc_' . $clave, true );
		$ph  = isset( $c['ph'] ) ? $c['ph'] : '';
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $c['label'] ) . '</label></th><td>';
		switch ( $c['type'] ) {
			case 'textarea':
			case 'lines':
				printf( '<textarea class="large-text" rows="4" id="%1$s" name="%1$s" placeholder="%2$s">%3$s</textarea>', esc_attr( $id ), esc_attr( $ph ), esc_textarea( $val ) );
				break;
			case 'url':
				printf( '<input type="url" class="large-text" id="%1$s" name="%1$s" placeholder="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $ph ), esc_attr( $val ) );
				break;
			case 'select':
				$opts = call_user_func( $c['options'] );
				printf( '<select id="%1$s" name="%1$s">', esc_attr( $id ) );
				foreach ( $opts as $k => $etiqueta ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $val, $k, false ), esc_html( $etiqueta ) );
				}
				echo '</select>';
				break;
			case 'media':
				$nombre = $val ? basename( (string) get_attached_file( (int) $val ) ) : '';
				printf(
					'<div class="fc-media" data-mime="%4$s"><input type="hidden" id="%1$s" name="%1$s" value="%2$s"><span class="fc-media-nombre">%3$s</span> <button type="button" class="button fc-media-elegir">Elegir archivo</button> <button type="button" class="button-link fc-media-quitar"%5$s>Quitar</button></div>',
					esc_attr( $id ),
					esc_attr( $val ),
					$nombre ? esc_html( $nombre ) : '<em>Sin archivo</em>',
					esc_attr( isset( $c['mime'] ) ? $c['mime'] : '' ),
					$val ? '' : ' hidden'
				);
				break;
			default:
				printf( '<input type="text" class="regular-text" id="%1$s" name="%1$s" placeholder="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $ph ), esc_attr( $val ) );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action(
	'save_post',
	function ( $post_id, $post ) {
		$defs = fc_campos_definicion();
		if ( ! isset( $defs[ $post->post_type ] ) ) {
			return;
		}
		if ( ! isset( $_POST['fc_campos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fc_campos_nonce'] ) ), 'fc_guardar_campos' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( $defs[ $post->post_type ]['campos'] as $clave => $c ) {
			$nombre = 'fc_' . $clave;
			$crudo  = isset( $_POST[ $nombre ] ) ? wp_unslash( $_POST[ $nombre ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			switch ( $c['type'] ) {
				case 'textarea':
				case 'lines':
					$v = sanitize_textarea_field( $crudo );
					break;
				case 'url':
					$v = esc_url_raw( trim( $crudo ) );
					break;
				case 'select':
					$opts = call_user_func( $c['options'] );
					$v    = isset( $opts[ $crudo ] ) ? $crudo : '';
					break;
				case 'media':
					$v = absint( $crudo );
					$v = $v ? $v : '';
					break;
				default:
					$v = sanitize_text_field( $crudo );
			}
			if ( '' === $v ) {
				delete_post_meta( $post_id, '_fc_' . $clave );
			} else {
				update_post_meta( $post_id, '_fc_' . $clave, $v );
			}
		}
	},
	10,
	2
);

/* Selector de archivos de la biblioteca de medios para los campos «media». */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen  = get_current_screen();
		$ajustes = $screen && 'fc_cotizacion_page_fc-ajustes' === $screen->id;
		if ( ! $ajustes ) {
			if ( ! $screen || ! in_array( $screen->post_type, array_keys( fc_campos_definicion() ), true ) ) {
				return;
			}
			if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
				return;
			}
		}
		wp_enqueue_media();
		$js = <<<'JS'
jQuery(function($){
  $(document).on('click','.fc-media-elegir',function(e){
    e.preventDefault();
    var box=$(this).closest('.fc-media'), mime=box.data('mime')||'';
    var frame=wp.media({title:'Elegir archivo',button:{text:'Usar este archivo'},library:mime?{type:mime}:{},multiple:false});
    frame.on('select',function(){
      var a=frame.state().get('selection').first().toJSON();
      box.find('input').val(a.id); box.find('.fc-media-nombre').text(a.filename||a.title); box.find('.fc-media-quitar').prop('hidden',false);
    });
    frame.open();
  });
  $(document).on('click','.fc-media-quitar',function(e){
    e.preventDefault(); var box=$(this).closest('.fc-media');
    box.find('input').val(''); box.find('.fc-media-nombre').html('<em>Sin archivo</em>'); $(this).prop('hidden',true);
  });
});
JS;
		wp_add_inline_script( 'media-editor', $js );
	}
);
