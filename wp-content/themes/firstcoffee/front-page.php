<?php
/**
 * Portada: se arma con las secciones de template-parts/.
 */
get_header();
?>
<main id="top">
	<?php
	foreach ( array( 'hero', 'marquee', 'como-funciona', 'kit', 'beneficios', 'usos', 'rentabilidad', 'cotizador', 'faq' ) as $fc_parte ) {
		get_template_part( 'template-parts/' . $fc_parte );
	}
	?>
</main>
<?php
get_footer();
