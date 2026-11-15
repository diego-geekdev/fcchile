<?php
/**
 * Plugin Name: First Coffee — Núcleo del sitio
 * Description: Contenidos del sitio (pasos, piezas del kit, videos, preguntas), ajustes generales, cotizador con panel de cotizaciones y correos de aviso. Funciona junto al tema «First Coffee», pero los datos quedan aquí aunque cambie el tema.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: GeekDev — Diego Catalán
 * Author URI: https://geekdev.cl
 * Text Domain: fc-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FC_CORE_VERSION', '1.0.0' );
define( 'FC_CORE_FILE', __FILE__ );
define( 'FC_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FC_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FC_CORE_DIR . 'inc/helpers.php';
require_once FC_CORE_DIR . 'inc/cpt.php';
require_once FC_CORE_DIR . 'inc/roles.php';
require_once FC_CORE_DIR . 'inc/meta.php';
require_once FC_CORE_DIR . 'inc/ajustes.php';
require_once FC_CORE_DIR . 'inc/correos.php';
require_once FC_CORE_DIR . 'inc/rest-cotizacion.php';
require_once FC_CORE_DIR . 'inc/admin-cotizaciones.php';
require_once FC_CORE_DIR . 'inc/contenido-ejemplo.php';

register_activation_hook( __FILE__, 'fc_core_activar' );
register_deactivation_hook( __FILE__, 'fc_core_desactivar' );

function fc_core_activar() {
	fc_registrar_cpts();
	fc_roles_instalar();
	fc_carpeta_privada();
	flush_rewrite_rules();
}

function fc_core_desactivar() {
	flush_rewrite_rules();
}
