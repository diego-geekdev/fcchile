<?php
/**
 * Botón «Cargar contenido del sitio» (First Coffee → Ajustes).
 * Crea el contenido de First Coffee (versión nov 2026): pasos, componentes, beneficios, usos,
 * preguntas y las páginas La empresa, Ficha, Manual, Servicio, Brochures (+ Calle, Eventos, Negocios).
 *
 * Pasos, componentes, beneficios, usos y preguntas existentes se mueven a la papelera.
 * Las páginas solo se crean si no existe una con el mismo slug.
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
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( sprintf( 'Listo: se crearon %d contenidos y páginas. Revisa el menú First Coffee y Páginas.', $n ) ) );
		}
	}
);

function fc_cargar_contenido_ejemplo() {
	$n = 0;

	$crear = function ( $tipo, $titulo, $contenido, $orden, $meta = array(), $extra = array() ) use ( &$n ) {
		$id = wp_insert_post(
			array_merge(
				array(
					'post_type'    => $tipo,
					'post_status'  => 'publish',
					'post_title'   => $titulo,
					'post_content' => $contenido,
					'menu_order'   => $orden,
				),
				$extra
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

	/* Lo anterior de estos tipos va a la papelera (recuperable). */
	foreach ( array( 'fc_paso', 'fc_pieza', 'fc_faq', 'fc_beneficio', 'fc_uso' ) as $tipo ) {
		foreach ( get_posts( array( 'post_type' => $tipo, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $viejo ) {
			wp_trash_post( $viejo );
		}
	}

	/* Cómo funciona. */
	$pasos = array(
		array( 'Carga', 'Llena el estanque con café recién preparado y cierra la tapa.', 'Café recién hecho' ),
		array( 'Mochila', 'Pon el estanque de pie en la mochila térmica y pasa las mangueras por los ojales.', 'Va de pie' ),
		array( 'Presión', 'Unos golpes con la bomba manual de pie bastan: trabaja a menos de 0,5 bar.', 'Menos de 0,5 bar' ),
		array( 'Servicio', 'Abre el grifo y sirve. Cuando el chorro baje, dos o tres golpes más de bomba.', 'Vaso a vaso' ),
	);
	foreach ( $pasos as $i => $p ) {
		$crear( 'fc_paso', $p[0], $p[1], $i + 1, array( 'etiqueta' => $p[2] ) );
	}

	/* El equipo (componentes en la ilustración). */
	$crear( 'fc_pieza', 'Estanque de acero inoxidable', 'Estanque de 10 litros en acero AISI 304, con tapa hermética, sello de silicona y válvula de alivio con anillo para despresurizar.', 1, array( 'slot' => 'termo', 'etiqueta' => 'Estanque 10 L inox', 'specs' => "Capacidad: 10 L, unos 35 vasos por carga\nAcero inoxidable 304 en contacto con el café\nTapa hermética y válvula de alivio" ) );
	$crear( 'fc_pieza', 'Mochila térmica', 'Base rígida, aislación de espuma elastomérica de 25 mm con lámina aluminizada, bolsillo para la bomba y arnés acolchado con cinturón lumbar.', 2, array( 'slot' => 'mochila', 'etiqueta' => 'Aislación 25 mm', 'specs' => "Aislación de 25 mm con lámina aluminizada\nArnés acolchado y cinturón lumbar\nPeso cargado: unos 15 kg" ) );
	$crear( 'fc_pieza', 'Bomba y línea de servicio', 'Bombín manual de pie para dar presión, válvula antirretorno, conectores de desconexión rápida y manguera de 6 mm con grifo o pistola de palanca.', 3, array( 'slot' => 'piezas', 'etiqueta' => 'Grifo y bomba de aire', 'specs' => "Bombín manual de pie, sin electricidad\nConectores de desconexión rápida\nManguera de grado alimentario y grifo de palanca" ) );

	/* Por qué. */
	$beneficios = array(
		array( 'Café caliente', 'Aislación de 25 mm con lámina aluminizada y estanque de acero inoxidable.', 'calor' ),
		array( 'Manos libres', 'El peso va en la espalda, con arnés acolchado y cinturón lumbar. Tus manos quedan para servir.', 'manos' ),
		array( 'Sin electricidad', 'La presión la da una bomba manual de pie. Sin enchufe, sin baterías, sin carro.', 'energia' ),
		array( 'Fácil de limpiar', 'La tapa abre completa y los conectores se sueltan a mano.', 'limpieza' ),
		array( 'Ingeniería chilena', 'Diseñado y armado en Chile, con repuestos y soporte directo.', 'chile' ),
		array( 'Listo para usar', 'Llega armado, probado y con su manual de operación.', 'listo' ),
	);
	foreach ( $beneficios as $i => $b ) {
		$crear( 'fc_beneficio', $b[0], $b[1], $i + 1, array( 'icono' => $b[2] ) );
	}

	/* Páginas. Se crean primero los brochures para enlazarlos desde los usos. */
	$pag = function ( $slug, $titulo, $extracto, $html, $padre = 0, $orden = 0 ) use ( &$n ) {
		$existe = get_page_by_path( $padre ? get_post_field( 'post_name', $padre ) . '/' . $slug : $slug );
		if ( $existe ) {
			return $existe->ID;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $titulo,
				'post_name'    => $slug,
				'post_excerpt' => $extracto,
				'post_content' => $html,
				'post_parent'  => $padre,
				'menu_order'   => $orden,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$n++;
			return $id;
		}
		return 0;
	};
	$p = fc_contenido_paginas();
	$ids = array();
	foreach ( array( 'la-empresa', 'ficha', 'manual', 'servicio', 'brochures' ) as $i => $slug ) {
		$ids[ $slug ] = $pag( $slug, $p[ $slug ][0], $p[ $slug ][1], $p[ $slug ][2], 0, $i + 1 );
	}
	foreach ( array( 'calle', 'eventos', 'negocios' ) as $i => $slug ) {
		$ids[ $slug ] = $pag( $slug, $p[ $slug ][0], $p[ $slug ][1], $p[ $slug ][2], $ids['brochures'], $i + 1 );
	}
	$link = function ( $slug ) use ( $ids ) {
		return ! empty( $ids[ $slug ] ) ? get_permalink( $ids[ $slug ] ) : '';
	};
	/* Resuelve los enlaces {{url:slug}} entre páginas con su URL real. */
	foreach ( $ids as $pid ) {
		if ( ! $pid ) {
			continue;
		}
		$c = get_post_field( 'post_content', $pid );
		if ( false === strpos( $c, '{{url:' ) ) {
			continue;
		}
		$c = preg_replace_callback(
			'/\{\{url:([a-z\-]+)\}\}/',
			function ( $m ) use ( $link ) {
				$u = $link( $m[1] );
				return esc_url( $u ? $u : home_url( '/' ) );
			},
			$c
		);
		wp_update_post( array( 'ID' => $pid, 'post_content' => $c ) );
	}

	/* Dónde se usa. */
	$usos = array(
		array( 'Café en la calle', 'Tu punto de café en la espalda: te mueves a donde está la gente, sin carro ni arriendo de local.', 'Paraderos, salidas de metro, oficinas, ferias', 'Se paga en ≈ 10,5 jornadas', 'calle' ),
		array( 'Eventos', 'Café caliente servido a la mano donde no hay enchufe ni espacio para una barra. Una segunda carga cubre unos 70 invitados.', 'Matrimonios, ferias, deporte, empresas', 'Se paga en ≈ 3,7 eventos de 100 vasos', 'eventos' ),
		array( 'Oficinas y locales', 'Café en cada turno sin comprar una cafetera industrial, y se mueve a otra sala, obra o feria.', 'Recepción, turnos, clínicas, obras', 'Se paga en ≈ 18 días con 20 vasos diarios', 'negocios' ),
		array( 'Reparto a pie o en bici', 'Lleva el café al cliente: oficinas, condominios o despachos en la mañana.', 'Rutas de reparto, condominios, empresas', 'Sin carro, sin enchufe', '' ),
	);
	foreach ( $usos as $i => $u ) {
		$crear( 'fc_uso', $u[0], $u[1], $i + 1, array_filter( array( 'lugares' => $u[2], 'recupera' => $u[3], 'enlace' => $u[4] ? $link( $u[4] ) : '' ) ) );
	}

	/* Preguntas frecuentes. */
	$faq = array(
		array( '¿Necesita electricidad?', 'No. La presión la da una bomba manual de pie; no usa enchufe ni baterías.' ),
		array( '¿Cuánto café lleva?', 'El estanque es de 10 litros: unos 35 vasos por carga. Vendiendo en la calle, un estanque se termina en unas dos horas.' ),
		array( '¿Cuánto pesa?', 'Cargado, unos 15 kg. La mochila tiene arnés acolchado y cinturón lumbar para repartir el peso.' ),
		array( '¿Cuánto tiempo mantiene el café caliente?', 'Lo estamos validando. La meta de diseño es servir sobre 70 °C durante 2 horas; publicaremos el resultado de las pruebas.' ),
		array( '¿Alcanza para un evento?', 'Una carga rinde unos 35 vasos y una segunda carga cubre un evento de unas 70 personas. Con un segundo estanque listo, el cambio toma un minuto.' ),
		array( '¿Qué permisos necesito para vender?', 'Vender café requiere autorización sanitaria y, en la vía pública, el permiso municipal de tu comuna. Te orientamos al momento de la compra.' ),
		array( '¿Qué incluye y cómo lo compro?', 'Estanque de 10 L, mochila térmica, bomba de pie, manguera y grifo, demostración de uso, manual y soporte por WhatsApp. Escríbenos o completa el cotizador y te enviamos precio, plazo de entrega y forma de compra.' ),
	);
	foreach ( $faq as $i => $f ) {
		$crear( 'fc_faq', $f[0], $f[1], $i + 1 );
	}

	/* Ajustes: textos nuevos de portada y valores de referencia. Se conservan contacto y correos. */
	$aj  = get_option( 'fc_ajustes', array() );
	$def = fc_ajustes_defecto();
	foreach ( array( 'hero_titulo', 'hero_bajada', 'marquee', 'kit_bajada', 'precio_equipo', 'precio_vaso', 'vasos_carga', 'margen', 'jornadas_semana' ) as $k ) {
		$aj[ $k ] = $def[ $k ];
	}
	if ( empty( $aj['whatsapp'] ) ) {
		$aj['whatsapp'] = '+56 9 4679 0729';
	}
	update_option( 'fc_ajustes', $aj );

	return $n;
}

/** Contenido de las páginas internas: slug => [título, extracto, html]. */
function fc_contenido_paginas() {
	$wa = '[fc_whatsapp]';
	return array(
		'la-empresa' => array(
			'La empresa',
			'Vendemos café caliente en la calle desde una mochila. Y construimos la máquina con la que lo hacemos.',
			'<h2>Café de grano, servido vaso a vaso</h2>
<p>First Coffee nació en la calle: un estanque de acero inoxidable dentro de una mochila térmica, café recién preparado y servicio vaso a vaso, sin carro ni local. Aceptamos pago con tarjeta, así que nadie se queda sin café por no andar con efectivo.</p>
<div class="fc-cifras"><div><b>$2.490</b><span>el vaso</span></div><div><b>35</b><span>vasos por estanque</span></div><div><b>≈ 2 h</b><span>para vender un estanque</span></div></div>
<h2>Ingeniería chilena, probada vendiendo</h2>
<p>Un equipo de ingeniería chileno diseñó y armó la máquina, y la probó saliendo a vender todos los días. Cada ajuste del estanque, la mochila y la línea de servicio salió de esa experiencia.</p>
<h2>La misma máquina, para tu negocio</h2>
<p>Hoy vendemos el mismo equipo que usamos: probado, con manual de operación y soporte por WhatsApp. Precio de referencia: <strong>[fc_precio]</strong>.</p>
<p>' . $wa . '</p>
<p class="fc-nota">Valores referenciales en pesos chilenos, sujetos a confirmación en la cotización.</p>',
		),
		'ficha'      => array(
			'Ficha y rentabilidad',
			'Datos técnicos del equipo, componentes y en cuánto tiempo se paga según cómo lo uses.',
			'<h2>Resumen</h2>
<div class="fc-tabla-wrap"><table class="fc-tabla"><tbody>
<tr><th>Precio de lista</th><td>[fc_precio] <span class="fc-sub">Referencial, se confirma en la cotización</span></td></tr>
<tr><th>Capacidad</th><td>10 litros, unos 35 vasos por carga</td></tr>
<tr><th>Energía</th><td>Ninguna: bomba manual de pie</td></tr>
<tr><th>Recuperación</th><td>≈ 2 semanas vendiendo un estanque por jornada</td></tr>
</tbody></table></div>
<h2>Ficha técnica</h2>
<div class="fc-tabla-wrap"><table class="fc-tabla"><tbody>
<tr><th>Estanque</th><td>10 L, acero inoxidable AISI 304, tapa hermética</td></tr>
<tr><th>Peso cargado</th><td>Unos 15 kg</td></tr>
<tr><th>Presión de servicio</th><td>Menos de 0,5 bar</td></tr>
<tr><th>En contacto con el café</th><td>Acero inoxidable 304, silicona y manguera de grado alimentario</td></tr>
<tr><th>Aislación</th><td>Espuma elastomérica de 25 mm con lámina aluminizada</td></tr>
<tr><th>Conservación del calor</th><td>En validación. Meta de diseño: servir sobre 70 °C durante 2 horas (no es una garantía)</td></tr>
</tbody></table></div>
<h2>Componentes</h2>
<ul class="fc-lista">
<li><strong>Estanque:</strong> AISI 304 de 10 L, tapa hermética con sello de silicona.</li>
<li><strong>Conectores de desconexión rápida:</strong> para la línea de aire y la de líquido.</li>
<li><strong>Válvula antirretorno:</strong> en la línea de aire.</li>
<li><strong>Línea de servicio:</strong> manguera de 6 mm, abrazaderas inoxidables y grifo o pistola de palanca.</li>
<li><strong>Bomba de aire:</strong> bombín manual de pie.</li>
<li><strong>Válvula de alivio:</strong> en la tapa, con anillo para despresurizar a mano.</li>
<li><strong>Mochila térmica:</strong> base rígida, ojales de 30 y 20 mm, bolsillo para la bomba, arnés acolchado y cinturón lumbar.</li>
</ul>
<h2 id="escenarios">¿En cuántos días se paga?</h2>
[fc_escenarios]
<h2 id="calculadora">Calcula con tus números</h2>
[fc_calculadora]
<h2>Garantía y servicio</h2>
<p>Se entrega con demostración de uso, manual de operación y soporte por WhatsApp. Las condiciones de garantía y repuestos están en <a href="{{url:servicio}}">Servicio y repuestos</a>.</p>
<p>' . $wa . '</p>',
		),
		'manual'     => array(
			'Manual de operación',
			'Seguridad, uso diario, limpieza y solución de problemas del equipo First Coffee.',
			'<div class="fc-aviso"><h3>Antes de todo: seguridad</h3><ul>
<li>El café está caliente y bajo presión: puede quemar.</li>
<li>Nunca abras la tapa ni sueltes una línea con el estanque presurizado. Primero libera la presión con el anillo de la válvula.</li>
<li>Usa solo la bomba manual. Nunca un compresor eléctrico ni el aire de una bencinera.</li>
<li>No llenes el estanque hasta el borde: deja aire arriba.</li>
<li>No apuntes la manguera ni el grifo a una persona.</li>
<li>Solo café o agua. Nada de bebidas con gas ni otros líquidos.</li>
<li>Trabaja a menos de 0,5 bar. Si tienes que bombear mucho, hay una fuga.</li>
</ul></div>
<h2>Antes de salir</h2>
<ol class="fc-pasos">
<li><strong>Revisa el equipo:</strong> sello de la tapa, mangueras, abrazaderas y conectores.</li>
<li><strong>Precalienta el estanque:</strong> llénalo con agua caliente unos 10 minutos y vacíalo.</li>
<li><strong>Carga el café:</strong> recién hecho y bien filtrado, hasta unos 9 de los 10 litros. Deja al menos 1 litro de aire.</li>
<li><strong>Cierra la tapa:</strong> con el sello en su lugar, pareja y firme.</li>
<li><strong>Conecta las líneas:</strong> el conector de líquido en el poste de líquido y el de aire en el de aire. Tira suave para confirmar.</li>
<li><strong>Arma la mochila:</strong> estanque de pie, mangueras por los ojales, grifo a mano y bomba en el bolsillo lateral.</li>
</ol>
<h2>Durante el servicio</h2>
<ol class="fc-pasos" start="7">
<li><strong>Da presión:</strong> 4 o 5 golpes de bomba. El café debe salir en un chorro suave.</li>
<li><strong>Sirve:</strong> abre el grifo con la palanca, llena el vaso y cierra. Una presurización alcanza para varios vasos.</li>
<li><strong>Mantén el flujo:</strong> cuando el chorro baje, 2 o 3 golpes más.</li>
</ol>
<h2>Al terminar la jornada</h2>
<ol class="fc-pasos">
<li>Libera la presión con el anillo de la válvula de alivio.</li>
<li>Desconecta los conectores de aire y de líquido.</li>
<li>Abre la tapa con cuidado: puede salir vapor.</li>
<li>Vacía el estanque y enjuágalo varias veces con agua caliente.</li>
<li>Lávalo con detergente suave y esponja no abrasiva, y enjuaga bien.</li>
<li>Pasa agua caliente por la manguera y el grifo.</li>
<li>Déjalo secar abierto y boca abajo, con la tapa suelta.</li>
</ol>
<p class="fc-nota">Los aceites del café dejan residuos: lavar el mismo día evita el olor a rancio y el café amargo.</p>
<h2>Mantención</h2>
<div class="fc-tabla-wrap"><table class="fc-tabla"><tbody>
<tr><th>Diaria</th><td>Lavado completo y revisar que el grifo no tenga restos de café.</td></tr>
<tr><th>Semanal</th><td>Revisar el sello de la tapa y los conectores; un poco de grasa de silicona grado alimentario en el sello.</td></tr>
<tr><th>Mensual</th><td>Revisar mangueras y abrazaderas. Cambia la manguera si se endurece, amarillea o se agrieta.</td></tr>
<tr><th>Si algo falla</th><td>Reemplaza el sello o conector por un repuesto original First Coffee.</td></tr>
</tbody></table></div>
<h2>Solución de problemas</h2>
<div class="fc-tabla-wrap"><table class="fc-tabla"><thead><tr><th>Problema</th><th>Qué hacer</th></tr></thead><tbody>
<tr><td>No sale café</td><td>Revisa que el grifo esté abierto y que ninguna manguera esté doblada. Da 3 golpes más de bomba.</td></tr>
<tr><td>Bombeo y no sube la presión</td><td>Hay una fuga de aire. Pasa agua con jabón por la tapa, conectores y uniones mientras bombeas: las burbujas muestran la fuga.</td></tr>
<tr><td>Sale poco café</td><td>Queda poco en el estanque o falta presión. Da 2 o 3 golpes más.</td></tr>
<tr><td>Sale aire o espuma</td><td>El estanque está vacío. Cierra el grifo y recarga.</td></tr>
<tr><td>Gotea la tapa</td><td>Libera la presión, limpia y vuelve a asentar el sello; cámbialo si está dañado.</td></tr>
<tr><td>Gotea un conector</td><td>Libera la presión y reconecta. Si sigue, cambia el sello.</td></tr>
<tr><td>La manguera se ablanda o deforma</td><td>Está muy caliente para esa manguera. Cámbiala por silicona grado alimentario y evita doblarla.</td></tr>
<tr><td>El café llega frío</td><td>Precalienta el estanque antes de cargar y cierra bien la mochila.</td></tr>
</tbody></table></div>
<div class="fc-aviso"><p>Si el problema no está en la lista, deja de usar el equipo y escríbenos por WhatsApp antes de seguir.</p><p>[fc_whatsapp texto="Escribir a soporte" mensaje="Hola, necesito ayuda con mi equipo First Coffee"]</p></div>
<p>[fc_imprimir]</p>',
		),
		'servicio'   => array(
			'Servicio y repuestos',
			'Qué incluye la entrega, cómo funciona el soporte y cuándo cambiar cada repuesto.',
			'<h2>Qué recibes</h2>
<p>El equipo llega armado y probado: estanque, mochila, conectores, mangueras, grifo y bomba manual. Además, una demostración de uso y el <a href="{{url:manual}}">manual de operación</a>.</p>
<h2>Soporte</h2>
<p>Si algo falla, mándanos una foto o un video por WhatsApp y te decimos la causa probable y qué hacer. Tenemos repuestos originales. Las condiciones de garantía van en la cotización de cada equipo.</p>
<p>[fc_whatsapp texto="Pedir soporte por WhatsApp" mensaje="Hola, necesito soporte para mi equipo First Coffee"]</p>
<h2>Repuestos y cuándo cambiarlos</h2>
<div class="fc-tabla-wrap"><table class="fc-tabla"><thead><tr><th>Repuesto</th><th>Cámbialo si…</th></tr></thead><tbody>
<tr><td>Sello de la tapa</td><td>Se reseca, se agrieta o gotea (revísalo cada semana).</td></tr>
<tr><td>Sellos de los conectores</td><td>Gotean al conectar.</td></tr>
<tr><td>Conectores de líquido y aire</td><td>No calzan firme o se traban.</td></tr>
<tr><td>Válvula antirretorno</td><td>Baja la presión al soltar la bomba.</td></tr>
<tr><td>Manguera de servicio</td><td>Se endurece, amarillea o se agrieta.</td></tr>
<tr><td>Abrazaderas inoxidables</td><td>Se oxidan o se sueltan.</td></tr>
<tr><td>Grifo o pistola de palanca</td><td>Gotea o la palanca no cierra bien.</td></tr>
<tr><td>Bomba manual</td><td>Pierde aire o no da presión.</td></tr>
</tbody></table></div>
<h2>Preguntas frecuentes</h2>
<ul class="fc-lista">
<li><strong>¿Necesita electricidad?</strong> No, la presión la da una bomba manual.</li>
<li><strong>¿Cuánto café lleva?</strong> 10 litros, unos 35 vasos por carga.</li>
<li><strong>¿Cuánto pesa?</strong> Unos 15 kg cargado, con arnés acolchado y cinturón lumbar.</li>
<li><strong>¿Cuánto mantiene el calor?</strong> Lo publicaremos cuando terminen las pruebas.</li>
<li><strong>¿Sirve para eventos?</strong> Sí: una segunda carga cubre un evento de unas 70 personas.</li>
<li><strong>¿Qué permisos necesito?</strong> Autorización sanitaria y el permiso municipal de tu comuna.</li>
</ul>',
		),
		'brochures'  => array(
			'Brochures',
			'Elige el que calza con tu caso y compártelo por WhatsApp o guárdalo como PDF.',
			'[fc_brochures]
<h2>Documentos</h2>
<ul class="fc-lista">
<li><a href="{{url:ficha}}"><strong>Ficha técnica y financiera</strong></a>: especificaciones, componentes y calculadora.</li>
<li><a href="{{url:manual}}"><strong>Manual de operación</strong></a>: seguridad, uso, limpieza y problemas.</li>
<li><a href="{{url:servicio}}"><strong>Servicio y repuestos</strong></a>: entrega, soporte y garantía.</li>
</ul>
<p>' . $wa . '</p>',
		),
		'calle'      => array(
			'Brochure: café en la calle',
			'Tu punto de café, en la espalda. Para vender en paraderos, salidas de metro, oficinas y ferias.',
			'<h2>El problema</h2>
<ul class="fc-lista"><li>Los termos se enfrían y a la segunda hora el café está tibio.</li><li>Hay que cargar varios termos pesados, y no alcanzan.</li><li>Un carro o un local cuesta mucho más que la máquina y te deja en un solo lugar.</li></ul>
<h2>La solución</h2>
<p>Estanque de acero inoxidable de 10 L dentro de una mochila aislada. Le das presión con la bomba de pie y el café sale por la manguera. Sin electricidad.</p>
<ol class="fc-pasos"><li><strong>Carga</strong> el café recién preparado.</li><li><strong>Póntelo</strong> en la mochila, con arnés acolchado.</li><li><strong>Da presión</strong> con unos golpes de bomba (menos de 0,5 bar).</li><li><strong>Sirve</strong> abriendo el grifo y muévete a donde está la gente.</li></ol>
<h2>Los números</h2>
<div class="fc-cifras"><div><b>~35</b><span>vasos por estanque</span></div><div><b>~2 h</b><span>por estanque</span></div><div><b>$2.490</b><span>el vaso</span></div><div><b>$1.494</b><span>margen por vaso (60%)</span></div></div>
[fc_escenarios]
<h2>Qué incluye</h2>
<ul class="fc-lista"><li>Estanque de acero inoxidable de 10 L.</li><li>Mochila térmica con aislación de 25 mm, arnés y cinturón lumbar.</li><li>Bomba de pie, manguera de servicio y grifo de palanca.</li><li>Demostración de uso y manual de operación.</li><li>Soporte por WhatsApp y repuestos originales.</li></ul>
<p class="fc-nota">Vender café en la vía pública requiere autorización sanitaria y permiso municipal de tu comuna; te orientamos en la compra. Escenarios ilustrativos; valores en pesos chilenos sujetos a confirmación.</p>
<p>[fc_whatsapp texto="Quiero el mío" mensaje="Hola, quiero un equipo First Coffee para vender en la calle"] [fc_imprimir]</p>',
		),
		'eventos'    => array(
			'Brochure: eventos',
			'Café caliente para tus invitados, sin enchufes. Servido a la mano donde no hay electricidad ni espacio para una barra.',
			'<h2>El problema</h2>
<ul class="fc-lista"><li>Las cafeteras eléctricas necesitan enchufe, mesa y tiempo para calentar.</li><li>Los termos se vacían rápido y el café se enfría.</li><li>Un barista con equipo ocupa espacio y sube el costo del evento.</li></ul>
<h2>Cómo se usa en un evento</h2>
<ol class="fc-pasos"><li><strong>Carga:</strong> llenas el estanque en la cocina y lo llevas listo.</li><li><strong>En movimiento:</strong> quien sirve camina entre los invitados con la mochila.</li><li><strong>Presión:</strong> unos golpes de bomba mantienen el flujo.</li><li><strong>Recarga:</strong> con un segundo estanque cargado, el cambio toma un minuto.</li></ol>
<h2>Los números</h2>
<div class="fc-cifras"><div><b>~35</b><span>vasos por carga</span></div><div><b>~70</b><span>vasos con dos cargas</span></div><div><b>$249.000</b><span>venta en un evento de 100 vasos</span></div><div><b>3,7</b><span>eventos para pagar el equipo</span></div></div>
[fc_escenarios]
<h2>Qué incluye</h2>
<ul class="fc-lista"><li>Estanque de acero inoxidable de 10 L.</li><li>Mochila térmica aislada.</li><li>Bomba de pie, manguera de servicio y grifo de palanca.</li><li>Manual de operación y demostración de uso.</li><li>Soporte por WhatsApp y repuestos originales.</li></ul>
<p class="fc-nota">Para vender café en eventos se necesita la autorización sanitaria correspondiente. Escenarios ilustrativos; valores en pesos chilenos sujetos a confirmación.</p>
<p>[fc_whatsapp texto="Cotizar para eventos" mensaje="Hola, quiero cotizar un equipo First Coffee para eventos"] [fc_imprimir]</p>',
		),
		'negocios'   => array(
			'Brochure: negocios',
			'Café caliente en cada turno, sin comprar una cafetera industrial. Para oficinas, locales, clínicas, obras y puntos de venta.',
			'<h2>El problema</h2>
<ul class="fc-lista"><li>Las cafeteras fijas cuestan más, ocupan espacio y piden instalación eléctrica.</li><li>No se pueden llevar a otra sala, obra o feria.</li><li>Necesitan mantención y repuestos de marcas específicas.</li></ul>
<h2>Cómo se usa</h2>
<ol class="fc-pasos"><li><strong>Prepara</strong> el café como siempre.</li><li><strong>Carga</strong> el estanque de 10 L.</li><li><strong>Sirve</strong> en la sala de reuniones, la recepción, los turnos o el punto de venta.</li><li><strong>Limpia</strong> al terminar y queda listo para el día siguiente.</li></ol>
<h2>Los números</h2>
<div class="fc-cifras"><div><b>~35</b><span>vasos por carga, una mañana de oficina</span></div><div><b>0</b><span>consumo eléctrico</span></div><div><b>18,4</b><span>días para pagarse con 20 vasos diarios</span></div><div><b>$657.360</b><span>margen al mes con 20 vasos (22 días)</span></div></div>
[fc_escenarios]
<h2>Qué incluye</h2>
<ul class="fc-lista"><li>Estanque de acero inoxidable de 10 L.</li><li>Mochila térmica aislada.</li><li>Bomba de pie, manguera y grifo de palanca.</li><li>Manual de operación y demostración de uso.</li><li>Soporte por WhatsApp y repuestos originales.</li></ul>
<p class="fc-nota">El margen de 60% es un supuesto. Si el café es para uso interno no hay venta al público; para vender se necesita la autorización sanitaria correspondiente.</p>
<p>[fc_whatsapp texto="Cotizar para mi negocio" mensaje="Hola, quiero cotizar un equipo First Coffee para mi negocio"] [fc_imprimir]</p>',
		),
	);
}
