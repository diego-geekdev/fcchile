<?php
/**
 * Correos de una cotización nueva: aviso a First Coffee y copia al cliente.
 * Usa wp_mail(): en producción conviene configurar SMTP (por ejemplo FluentSMTP con la cuenta de Google Workspace).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Datos de una cotización como lista ordenada etiqueta => valor. */
function fc_cotizacion_resumen( $post_id ) {
	$logo = fc_campo( $post_id, 'logo' );
	if ( 'si' === $logo ) {
		$logo = fc_campo( $post_id, 'logo_nombre' ) ? 'Sí — ' . fc_campo( $post_id, 'logo_nombre' ) : 'Sí (lo enviará después)';
	} else {
		$logo = 'Sin logo';
	}
	$datos = array(
		'Dónde venderá'     => fc_campo( $post_id, 'uso', '—' ),
		'Equipos'           => fc_campo( $post_id, 'kits' ),
		'Logo'              => $logo,
		'Comentarios'       => fc_campo( $post_id, 'comentarios', 'Sin comentarios' ),
		'Nombre'            => fc_campo( $post_id, 'nombre' ),
		'WhatsApp'          => fc_campo( $post_id, 'whatsapp' ),
		'Correo'            => fc_campo( $post_id, 'correo' ),
		'Comuna o ciudad'   => fc_campo( $post_id, 'comuna' ),
		'Nos conoció por'   => fc_campo( $post_id, 'origen', 'Sin respuesta' ),
	);
	if ( 'Sin logo' === $logo ) {
		unset( $datos['Logo'] ); // El cotizador actual no pide logo.
	}
	return $datos;
}

function fc_enviar_correos_cotizacion( $post_id ) {
	$resumen = fc_cotizacion_resumen( $post_id );
	$lineas  = '';
	foreach ( $resumen as $k => $v ) {
		$lineas .= $k . ': ' . $v . "\n";
	}
	$sitio  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$nombre = fc_campo( $post_id, 'nombre' );
	$correo = fc_campo( $post_id, 'correo' );

	/* 1. Aviso interno. */
	$destino = fc_opt( 'correo_avisos', get_option( 'admin_email' ) );
	$destino = array_filter( array_map( 'trim', explode( ',', $destino ) ) );
	$asunto  = sprintf( 'Nueva cotización: %s (%s · %s)', $nombre, fc_campo( $post_id, 'kits' ), fc_campo( $post_id, 'uso', '' ) );
	$cuerpo  = "Llegó una cotización desde el sitio.\n\n" . $lineas;
	$cuerpo .= "\nVerla en el escritorio: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
	$wa      = preg_replace( '/\D/', '', fc_campo( $post_id, 'whatsapp' ) );
	if ( $wa ) {
		$cuerpo .= 'Responder por WhatsApp: https://wa.me/' . ( 9 === strlen( $wa ) ? '56' . $wa : $wa ) . "\n";
	}
	$cabeceras = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $correo ) ) {
		$cabeceras[] = 'Reply-To: ' . $nombre . ' <' . $correo . '>';
	}
	$adjuntos = array();
	$ruta     = fc_cotizacion_logo_ruta( $post_id );
	if ( $ruta && filesize( $ruta ) <= 5 * MB_IN_BYTES ) {
		$adjuntos[] = $ruta;
	}
	$ok_interno = $destino ? wp_mail( $destino, $asunto, $cuerpo, $cabeceras, $adjuntos ) : false;

	/* 2. Copia al cliente. */
	$ok_cliente = false;
	if ( is_email( $correo ) ) {
		$plazo  = fc_opt( 'plazo_respuesta' );
		$asunto = 'Recibimos tu cotización — ' . $sitio;
		$cuerpo = sprintf( "Hola, %s:\n\nRecibimos tu solicitud de cotización y te respondemos %s por WhatsApp o por este correo.\n\nEsto es lo que nos enviaste:\n\n%s\nSi quieres cambiar algo, responde este correo.\n\n%s\n%s\n", $nombre, $plazo, $lineas, $sitio, home_url( '/' ) );
		$cab    = array( 'Content-Type: text/plain; charset=UTF-8' );
		$pub    = fc_opt( 'correo_publico' );
		if ( is_email( $pub ) ) {
			$cab[] = 'Reply-To: ' . $sitio . ' <' . $pub . '>';
		}
		$ok_cliente = wp_mail( $correo, $asunto, $cuerpo, $cab );
	}

	update_post_meta( $post_id, '_fc_correo_aviso', $ok_interno ? 'enviado' : 'falló' );
	update_post_meta( $post_id, '_fc_correo_cliente', $ok_cliente ? 'enviado' : ( is_email( $correo ) ? 'falló' : 'sin correo' ) );
}
