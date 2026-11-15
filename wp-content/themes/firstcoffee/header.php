<?php
/**
 * Cabecera: barra de progreso, intro con el logo, navegación y menú móvil.
 */
$fc_home  = is_front_page() ? '' : home_url( '/' );
$fc_links = array(
	'#como'   => 'Cómo funciona',
	'#kit'    => 'El kit',
	'#videos' => 'Videos',
	'#faq'    => 'Preguntas',
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="sr" href="#top">Saltar al contenido</a>

<div id="progress" aria-hidden="true"><span></span></div>

<?php if ( is_front_page() ) : ?>
<div id="intro" hidden>
	<div class="intro-in">
		<img class="intro-logo" src="<?php echo esc_url( fct_img( 'first-coffee-logo-blanco.png' ) ); ?>" alt="First Coffee" width="548" height="458">
		<div class="signal" aria-hidden="true"><i class="light r"></i><i class="light a"></i><i class="light g"></i></div>
	</div>
	<button class="skip" type="button" id="skip">Saltar</button>
</div>
<?php endif; ?>

<header id="nav">
	<div class="wrap nav-in">
		<a class="brand" href="<?php echo esc_url( is_front_page() ? '#top' : home_url( '/' ) ); ?>" aria-label="First Coffee, inicio">
			<img class="brand-mark" src="<?php echo esc_url( fct_img( 'first-coffee-isotipo-blanco.png' ) ); ?>" alt="" width="256" height="249">
		</a>
		<nav class="nav-links" aria-label="Principal">
			<?php foreach ( $fc_links as $ancla => $texto ) : ?>
				<a href="<?php echo esc_url( $fc_home . $ancla ); ?>"><?php echo esc_html( $texto ); ?></a>
			<?php endforeach; ?>
			<a class="btn small magnet" href="<?php echo esc_url( $fc_home . '#cotizar' ); ?>" data-cursor="Cotizar">Cotizar mi kit</a>
		</nav>
		<button class="menu-btn" type="button" id="menuBtn" aria-label="Abrir menú" aria-expanded="false">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M4 8h16M4 16h16"/></svg>
		</button>
	</div>
</header>

<div id="menu" hidden>
	<button class="menu-btn close" type="button" id="menuClose" aria-label="Cerrar menú" style="display:grid">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
	</button>
	<?php
	$fc_i = 0;
	foreach ( $fc_links as $ancla => $texto ) :
		?>
		<a href="<?php echo esc_url( $fc_home . $ancla ); ?>" style="--i:<?php echo (int) $fc_i++; ?>"><?php echo esc_html( $texto ); ?></a>
	<?php endforeach; ?>
	<a href="<?php echo esc_url( $fc_home . '#cotizar' ); ?>" style="--i:<?php echo (int) $fc_i; ?>;color:var(--paint)">Cotizar mi kit</a>
</div>
