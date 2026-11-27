<?php
/**
 * «Dónde se usa»: un recuadro por cada Uso (First Coffee → Usos).
 */
$fc_usos = fct_items( 'fc_uso', 6 );
if ( ! $fc_usos ) {
	return;
}
$fc_escena = array(
	'<div class="lanes l1"></div><div class="car c1"></div><div class="car c3"></div><div class="barista"></div>',
	'<div class="lanes l2"></div><div class="barista"></div><div class="car c4"></div>',
	'<div class="lanes l1"></div><div class="lanes l2"></div><div class="car c2"></div>',
	'<div class="lanes l1"></div><div class="barista"></div>',
);
?>
<section class="sec wrap" id="usos">
	<div class="sec-head rv" style="flex-direction:row;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:20px">
		<div style="display:flex;flex-direction:column;gap:18px"><span class="eyebrow">Dónde se usa</span><h2>Un equipo,<br>varios negocios.</h2></div>
		<a class="btn ghost small" href="<?php echo esc_url( fct_pagina_url( 'brochures' ) ); ?>">Ver brochures</a>
	</div>
	<div class="usos rv">
		<?php
		foreach ( $fc_usos as $fc_i => $fc_u ) :
			$fc_link  = fct_campo( $fc_u->ID, 'enlace' );
			$fc_tag   = $fc_link ? 'a' : 'article';
			$fc_thumb = has_post_thumbnail( $fc_u ) ? get_the_post_thumbnail_url( $fc_u, 'fc-panel' ) : '';
			?>
			<<?php echo $fc_tag; // phpcs:ignore ?> class="uso"<?php echo $fc_link ? ' href="' . esc_url( $fc_link ) . '" data-cursor="Ver"' : ''; ?>>
				<div class="uso-media">
					<?php if ( $fc_thumb ) : ?>
						<img class="media-cover" src="<?php echo esc_url( $fc_thumb ); ?>" alt="" loading="lazy">
					<?php else : ?>
						<div class="scene" aria-hidden="true"><?php echo $fc_escena[ $fc_i % 4 ]; // phpcs:ignore ?></div>
					<?php endif; ?>
				</div>
				<div class="uso-body">
					<?php if ( fct_campo( $fc_u->ID, 'lugares' ) ) : ?><span class="uso-lug"><?php echo esc_html( fct_campo( $fc_u->ID, 'lugares' ) ); ?></span><?php endif; ?>
					<h3><?php echo esc_html( get_the_title( $fc_u ) ); ?></h3>
					<p><?php echo esc_html( wp_strip_all_tags( $fc_u->post_content ) ); ?></p>
					<?php if ( fct_campo( $fc_u->ID, 'recupera' ) ) : ?><b class="uso-dato"><?php echo esc_html( fct_campo( $fc_u->ID, 'recupera' ) ); ?></b><?php endif; ?>
					<?php if ( $fc_link ) : ?><span class="uso-mas">Ver brochure →</span><?php endif; ?>
				</div>
			</<?php echo $fc_tag; // phpcs:ignore ?>>
		<?php endforeach; ?>
	</div>
</section>
