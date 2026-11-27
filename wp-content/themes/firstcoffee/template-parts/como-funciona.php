<?php
/**
 * «Cómo funciona»: un panel por cada Paso (First Coffee → Pasos), en el orden definido.
 */
$fc_pasos = fct_items( 'fc_paso' );
if ( ! $fc_pasos ) {
	return;
}
$fc_total  = count( $fc_pasos );
$fc_escena = array(
	'<div class="lanes l1"></div><div class="car c1"></div><div class="car c2"></div><div class="car c3"></div>',
	'<div class="lanes l2"></div><div class="car c3"></div><div class="barista"></div>',
	'<div class="lanes l1"></div><div class="car c2"></div><div class="barista"></div>',
	'<div class="lanes l1"></div><div class="lanes l2"></div><div class="car c4"></div>',
);
?>
<section class="howto" id="como" style="--pasos:<?php echo (int) $fc_total; ?>">
	<div class="howto-sticky">
		<div class="wrap howto-head">
			<div class="sec-head" style="margin:0">
				<span class="eyebrow">Cómo funciona</span>
				<h2>Cuatro pasos,<br>sin electricidad.</h2>
			</div>
			<span class="counter" aria-live="polite"><b id="hCount">01</b> / <?php echo esc_html( str_pad( (string) $fc_total, 2, '0', STR_PAD_LEFT ) ); ?></span>
		</div>
		<div class="stories-bars" id="bars" aria-hidden="true"><?php echo str_repeat( '<span><i></i></span>', $fc_total ); // phpcs:ignore ?></div>
		<div class="track-wrap">
			<div class="track" id="track">
				<button class="tapzone prev" type="button" id="tapPrev" aria-label="Paso anterior"></button>
				<button class="tapzone next" type="button" id="tapNext" aria-label="Paso siguiente"></button>
				<?php
				foreach ( $fc_pasos as $fc_i => $fc_p ) :
					$fc_clip  = (int) fct_campo( $fc_p->ID, 'video' );
					$fc_clipu = $fc_clip ? wp_get_attachment_url( $fc_clip ) : '';
					$fc_etq   = fct_campo( $fc_p->ID, 'etiqueta' );
					?>
					<article class="panel<?php echo 0 === $fc_i ? ' active' : ''; ?>">
						<div class="panel-media">
							<?php if ( $fc_clipu ) : ?>
								<video class="media-cover" src="<?php echo esc_url( $fc_clipu ); ?>" muted loop playsinline preload="metadata" <?php echo has_post_thumbnail( $fc_p ) ? 'poster="' . esc_url( get_the_post_thumbnail_url( $fc_p, 'fc-panel' ) ) . '"' : ''; ?>></video>
							<?php elseif ( has_post_thumbnail( $fc_p ) ) : ?>
								<?php echo get_the_post_thumbnail( $fc_p, 'fc-panel', array( 'class' => 'media-cover', 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="scene" aria-hidden="true"><?php echo $fc_escena[ $fc_i % 4 ]; // phpcs:ignore ?></div>
							<?php endif; ?>
							<?php if ( $fc_etq ) : ?><span class="clip"><?php echo esc_html( $fc_etq ); ?></span><?php endif; ?>
						</div>
						<div class="panel-body">
							<span class="panel-num">PASO <?php echo esc_html( str_pad( (string) ( $fc_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3><?php echo esc_html( get_the_title( $fc_p ) ); ?></h3>
							<p><?php echo esc_html( wp_strip_all_tags( $fc_p->post_content ) ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="road" aria-hidden="true"><div class="road-car" id="roadCar"><svg viewBox="0 0 24 24" fill="none" stroke="#080808" stroke-width="2.2" stroke-linejoin="round"><rect x="5" y="6" width="14" height="14" rx="3"/><path d="M8 6V4h8v2M9 11h6"/></svg></div></div>
	</div>
</section>
