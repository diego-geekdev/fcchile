<?php
/**
 * Hero: título (la última palabra en dorado), bajada, botones y video o imagen principal.
 */
$fc_titulo  = fct_opt( 'hero_titulo', 'Tu café en el taco.' );
$fc_palabras = preg_split( '/\s+/u', trim( $fc_titulo ) );
$fc_video   = (int) fct_opt( 'hero_video' );
$fc_poster  = (int) fct_opt( 'hero_poster' );
$fc_video_u = $fc_video ? wp_get_attachment_url( $fc_video ) : '';
$fc_poster_u = $fc_poster ? wp_get_attachment_image_url( $fc_poster, 'full' ) : '';
?>
<section class="hero wrap" id="hero">
	<div class="hero-top">
		<h1 id="claim" aria-label="<?php echo esc_attr( $fc_titulo ); ?>">
			<?php
			$fc_ult = count( $fc_palabras ) - 1;
			foreach ( $fc_palabras as $fc_i => $fc_p ) {
				printf( '<span class="w%s" style="--i:%d" aria-hidden="true">%s</span>', $fc_i === $fc_ult ? ' hl' : '', (int) $fc_i, esc_html( $fc_p ) );
			}
			?>
		</h1>
		<div class="hero-side">
			<p class="lead"><?php echo esc_html( fct_opt( 'hero_bajada' ) ); ?></p>
			<div class="hero-ctas">
				<a class="btn magnet" href="#cotizar" data-cursor="Cotizar">Cotizar mi kit</a>
				<a class="btn ghost" href="#como">Ver cómo funciona</a>
			</div>
			<span class="cue"><i></i>Avanza, está en verde ↓</span>
		</div>
	</div>
	<div class="hero-media" id="heroMedia">
		<?php if ( $fc_video_u ) : ?>
			<video class="media-cover" src="<?php echo esc_url( $fc_video_u ); ?>" <?php echo $fc_poster_u ? 'poster="' . esc_url( $fc_poster_u ) . '"' : ''; ?> autoplay muted loop playsinline preload="metadata" aria-label="Barista de First Coffee entre los autos"></video>
		<?php elseif ( $fc_poster_u ) : ?>
			<img class="media-cover" src="<?php echo esc_url( $fc_poster_u ); ?>" alt="Barista de First Coffee entre los autos" fetchpriority="high">
		<?php else : ?>
			<div class="scene" aria-hidden="true"><div class="lanes l1"></div><div class="lanes l2"></div><div class="car c1"></div><div class="car c2"></div><div class="car c3"></div><div class="car c4"></div><div class="barista"></div></div>
		<?php endif; ?>
	</div>
</section>
