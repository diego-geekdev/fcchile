<?php
/**
 * Permisos: administrador y rol «Gestor First Coffee».
 * El gestor ve cotizaciones y edita contenidos y ajustes del sitio, pero no plugins, temas ni usuarios.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fc_caps_contenidos() {
	return array(
		'edit_fc_contenidos',
		'edit_others_fc_contenidos',
		'edit_published_fc_contenidos',
		'edit_private_fc_contenidos',
		'publish_fc_contenidos',
		'read_private_fc_contenidos',
		'delete_fc_contenidos',
		'delete_others_fc_contenidos',
		'delete_published_fc_contenidos',
		'delete_private_fc_contenidos',
	);
}

function fc_caps_cotizaciones() {
	return array(
		'edit_fc_cotizaciones',
		'edit_others_fc_cotizaciones',
		'edit_published_fc_cotizaciones',
		'edit_private_fc_cotizaciones',
		'publish_fc_cotizaciones',
		'read_private_fc_cotizaciones',
		'delete_fc_cotizaciones',
		'delete_others_fc_cotizaciones',
		'delete_published_fc_cotizaciones',
		'delete_private_fc_cotizaciones',
	);
}

function fc_roles_instalar() {
	$todas = array_merge( fc_caps_contenidos(), fc_caps_cotizaciones(), array( 'manage_fc_ajustes' ) );

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( $todas as $cap ) {
			$admin->add_cap( $cap );
		}
	}

	$caps = array(
		'read'         => true,
		'upload_files' => true,
	);
	foreach ( $todas as $cap ) {
		$caps[ $cap ] = true;
	}
	remove_role( 'fc_gestor' );
	add_role( 'fc_gestor', 'Gestor First Coffee', $caps );

	update_option( 'fc_roles_version', FC_CORE_VERSION );
}

/* Si se actualiza el plugin sin reactivarlo, vuelve a instalar los permisos. */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'fc_roles_version' ) !== FC_CORE_VERSION ) {
			fc_roles_instalar();
		}
	}
);

/* Al gestor le simplificamos el escritorio: va directo a las cotizaciones. */
add_filter(
	'login_redirect',
	function ( $destino, $pedido, $usuario ) {
		if ( $usuario instanceof WP_User && in_array( 'fc_gestor', (array) $usuario->roles, true ) ) {
			return admin_url( 'edit.php?post_type=fc_cotizacion' );
		}
		return $destino;
	},
	10,
	3
);
