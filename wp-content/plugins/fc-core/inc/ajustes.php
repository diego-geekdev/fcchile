<?php
/**
 * Página «First Coffee → Ajustes»: datos que no se repiten (contacto, portada, números).
 * Todo se guarda en una sola opción: fc_ajustes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fc_ajustes_defecto() {
	return array(
		'whatsapp'        => '',
		'correo_publico'  => '',
		'correo_avisos'   => get_option( 'admin_email' ),
		'instagram'       => 'https://www.instagram.com/firstcoffeechile/',
		'tiktok'          => '',
		'hero_titulo'     => 'Tu café en el taco.',
		'hero_video'      => '',
		'hero_poster'     => '',
		'hero_bajada'     => 'Café de grano servido en tu ventanilla mientras esperas la luz verde. Y si quieres hacer lo mismo en tu ciudad, te armamos el kit.',
		'marquee'         => "Café de grano\nDirecto a tu ventanilla\nTu café en el taco",
		'kit_bajada'      => 'Mochila, termo y piezas armadas por nuestro mecánico. Es el mismo equipo que usamos todos los días en el taco.',
		'plazo_respuesta' => 'a la brevedad',
		'stat1_valor'     => '',
		'stat1_texto'     => 'seguidores en Instagram',
		'stat2_valor'     => '',
		'stat2_texto'     => 'el café de grano',
		'stat3_valor'     => '',
		'stat3_texto'     => '',
		'stat4_valor'     => '',
		'stat4_texto'     => '',
		'trabaja_texto'   => '',
	);
}

/** Secciones y campos de la página de ajustes. */
function fc_ajustes_campos() {
	return array(
		'contacto' => array(
			'titulo' => 'Contacto y redes',
			'campos' => array(
				'whatsapp'       => array( 'WhatsApp', 'text', '+56 9 1234 5678', 'Se usa en el botón flotante y en las preguntas frecuentes.' ),
				'correo_publico' => array( 'Correo público', 'email', 'hola@firstcoffeechile.cl', 'Se muestra en el pie de página.' ),
				'correo_avisos'  => array( 'Correo para avisos de cotización', 'email', '', 'Aquí llega cada cotización nueva. Puedes poner varios separados por coma.' ),
				'instagram'      => array( 'Instagram (URL)', 'url', 'https://www.instagram.com/firstcoffeechile/', '' ),
				'tiktok'         => array( 'TikTok (URL)', 'url', 'https://www.tiktok.com/@…', 'Si queda vacío, no se muestra.' ),
			),
		),
		'portada'  => array(
			'titulo' => 'Portada',
			'campos' => array(
				'hero_titulo'     => array( 'Título principal', 'text', 'Tu café en el taco.', 'La última palabra se destaca en dorado.' ),
				'hero_bajada'     => array( 'Bajada', 'textarea', '', '' ),
				'hero_video'      => array( 'Video principal (MP4 corto, en bucle y sin sonido)', 'media', 'video', 'Ideal: 10 a 20 segundos, horizontal, menos de 8 MB.' ),
				'hero_poster'     => array( 'Imagen principal', 'media', 'image', 'Se muestra mientras carga el video, o sola si no hay video.' ),
				'marquee'         => array( 'Frases de la cinta en movimiento', 'textarea', '', 'Una frase por línea.' ),
				'kit_bajada'      => array( 'Texto de la sección «El kit»', 'textarea', '', '' ),
				'plazo_respuesta' => array( 'Plazo de respuesta a cotizaciones', 'text', 'en 24 horas hábiles', 'Completa la frase «Te respondemos …».' ),
				'trabaja_texto'   => array( 'Texto «Trabaja con nosotros» (pie de página)', 'text', '¿Quieres ser barista en la calle? Escríbenos.', 'Si queda vacío, la columna no se muestra.' ),
			),
		),
		'numeros'  => array(
			'titulo' => 'Números («Lo que llevamos recorrido»)',
			'ayuda'  => 'Hasta 4 cifras. Si una queda sin valor, no se muestra; si todas quedan vacías, se oculta la sección. Las cifras como 6.800+ o $2.000 se animan solas.',
			'campos' => array(
				'stat1_valor' => array( 'Cifra 1', 'text', '6.800+', '' ),
				'stat1_texto' => array( 'Texto cifra 1', 'text', 'seguidores en Instagram', '' ),
				'stat2_valor' => array( 'Cifra 2', 'text', '$2.000', '' ),
				'stat2_texto' => array( 'Texto cifra 2', 'text', 'el café de grano', '' ),
				'stat3_valor' => array( 'Cifra 3', 'text', '', '' ),
				'stat3_texto' => array( 'Texto cifra 3', 'text', 'cafés vendidos', '' ),
				'stat4_valor' => array( 'Cifra 4', 'text', '', '' ),
				'stat4_texto' => array( 'Texto cifra 4', 'text', 'comunas donde estamos', '' ),
			),
		),
	);
}

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=fc_cotizacion',
			'Ajustes de First Coffee',
			'Ajustes',
			'manage_fc_ajustes',
			'fc-ajustes',
			'fc_ajustes_render'
		);
		add_submenu_page(
			'edit.php?post_type=fc_cotizacion',
			'Tipos de video',
			'Tipos de video',
			'edit_fc_contenidos',
			'edit-tags.php?taxonomy=fc_tipo_video&post_type=fc_video'
		);
	},
	20
);

/* Deja abierto el menú «First Coffee» al editar los tipos de video. */
add_filter(
	'parent_file',
	function ( $parent ) {
		$s = get_current_screen();
		if ( $s && 'fc_tipo_video' === $s->taxonomy ) {
			return 'edit.php?post_type=fc_cotizacion';
		}
		return $parent;
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'fc_ajustes',
			'fc_ajustes',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'fc_ajustes_sanitizar',
				'default'           => array(),
			)
		);
	}
);

/* Permite guardar a quien tenga manage_fc_ajustes (incluye al gestor). */
add_filter(
	'option_page_capability_fc_ajustes',
	function () {
		return 'manage_fc_ajustes';
	}
);

function fc_ajustes_sanitizar( $entrada ) {
	$limpio = array();
	if ( ! is_array( $entrada ) ) {
		return $limpio;
	}
	foreach ( fc_ajustes_campos() as $seccion ) {
		foreach ( $seccion['campos'] as $clave => $c ) {
			$v = isset( $entrada[ $clave ] ) ? (string) $entrada[ $clave ] : '';
			switch ( $c[1] ) {
				case 'url':
					$v = esc_url_raw( trim( $v ) );
					break;
				case 'email':
					$correos = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $v ) ) ) );
					$v       = implode( ', ', $correos );
					break;
				case 'textarea':
					$v = sanitize_textarea_field( $v );
					break;
				case 'media':
					$v = absint( $v ) ? (string) absint( $v ) : '';
					break;
				default:
					$v = sanitize_text_field( $v );
			}
			$limpio[ $clave ] = $v;
		}
	}
	return $limpio;
}

function fc_ajustes_render() {
	if ( ! current_user_can( 'manage_fc_ajustes' ) ) {
		return;
	}
	$guardado = get_option( 'fc_ajustes', array() );
	$def      = fc_ajustes_defecto();
	?>
	<div class="wrap">
		<h1>Ajustes de First Coffee</h1>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'fc_ajustes' ); ?>
			<?php foreach ( fc_ajustes_campos() as $seccion ) : ?>
				<h2 class="title"><?php echo esc_html( $seccion['titulo'] ); ?></h2>
				<?php if ( ! empty( $seccion['ayuda'] ) ) : ?>
					<p class="description"><?php echo esc_html( $seccion['ayuda'] ); ?></p>
				<?php endif; ?>
				<table class="form-table" role="presentation"><tbody>
				<?php
				foreach ( $seccion['campos'] as $clave => $c ) :
					list( $label, $tipo, $ph, $ayuda ) = $c;
					$id  = 'fc_' . $clave;
					$val = array_key_exists( $clave, $guardado ) ? $guardado[ $clave ] : ( isset( $def[ $clave ] ) ? $def[ $clave ] : '' );
					$name = 'fc_ajustes[' . $clave . ']';
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<?php if ( 'media' === $tipo ) : ?>
								<?php $nom = $val ? basename( (string) get_attached_file( (int) $val ) ) : ''; ?>
								<div class="fc-media" data-mime="<?php echo esc_attr( $ph ); ?>"><input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>"><span class="fc-media-nombre"><?php echo $nom ? esc_html( $nom ) : '<em>Sin archivo</em>'; ?></span> <button type="button" class="button fc-media-elegir">Elegir archivo</button> <button type="button" class="button-link fc-media-quitar"<?php echo $val ? '' : ' hidden'; ?>>Quitar</button></div>
							<?php elseif ( 'textarea' === $tipo ) : ?>
								<textarea class="large-text" rows="3" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $ph ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
							<?php else : ?>
								<input type="<?php echo 'url' === $tipo ? 'url' : 'text'; ?>" class="regular-text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $ph ); ?>" value="<?php echo esc_attr( $val ); ?>">
							<?php endif; ?>
							<?php if ( $ayuda ) : ?>
								<p class="description"><?php echo esc_html( $ayuda ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>
			<?php submit_button( 'Guardar ajustes' ); ?>
		</form>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<hr>
			<h2 class="title">Contenido de ejemplo</h2>
			<p class="description">Crea los pasos, piezas, videos y preguntas del prototipo para partir con algo que editar. Solo crea lo que esté vacío; no borra nada.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="fc_contenido_ejemplo">
				<?php wp_nonce_field( 'fc_contenido_ejemplo' ); ?>
				<?php submit_button( 'Cargar contenido de ejemplo', 'secondary', 'submit', false ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
