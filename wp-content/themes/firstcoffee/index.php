<?php
/**
 * Plantilla general (páginas legales, entradas o cualquier página que no sea la portada).
 */
get_header();
?>
<main id="top" class="sec wrap pagina">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'pagina-art' ); ?>>
				<h1><?php the_title(); ?></h1>
				<div class="pagina-cuerpo"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<h1>No encontramos esta página</h1>
		<p class="lead">Puede que el enlace esté mal escrito o que la página ya no exista.</p>
		<p style="margin-top:28px"><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Volver al inicio</a></p>
	<?php endif; ?>
</main>
<?php
get_footer();
