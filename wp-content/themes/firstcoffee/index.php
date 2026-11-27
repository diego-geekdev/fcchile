<?php
/**
 * Plantilla general: páginas internas (La empresa, Ficha, Manual, Servicio, Brochures…) y cualquier otra.
 * El extracto de la página se muestra como bajada bajo el título.
 */
get_header();
?>
<main id="top" class="pagina">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			$fc_padre = wp_get_post_parent_id( get_the_ID() );
			?>
			<article <?php post_class( 'pagina-art' ); ?>>
				<header class="pagina-head wrap">
					<span class="eyebrow">
						<?php if ( $fc_padre ) : ?>
							<a href="<?php echo esc_url( get_permalink( $fc_padre ) ); ?>"><?php echo esc_html( get_the_title( $fc_padre ) ); ?></a>
						<?php else : ?>
							First Coffee
						<?php endif; ?>
					</span>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>
				<div class="pagina-cuerpo wrap"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<header class="pagina-head wrap">
			<span class="eyebrow">Error 404</span>
			<h1>No encontramos esta página</h1>
			<p class="lead">Puede que el enlace esté mal escrito o que la página ya no exista.</p>
			<p style="margin-top:28px"><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Volver al inicio</a></p>
		</header>
	<?php endif; ?>
</main>
<?php
get_footer();
