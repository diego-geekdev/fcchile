<?php
/**
 * Mosaico de videos (First Coffee → Videos). Muestra los primeros 5 según el orden.
 */
$fc_videos = fct_items( 'fc_video', 5 );
if ( ! $fc_videos ) {
	return;
}
$fc_ig     = fct_opt( 'instagram', 'https://www.instagram.com/firstcoffeechile/' );
$fc_escena = array(
	'<div class="lanes l1"></div><div class="lanes l2"></div><div class="car c1"></div><div class="car c3"></div><div class="barista"></div>',
	'<div class="lanes l1"></div><div class="car c2"></div><div class="car c4"></div>',
	'<div class="lanes l2"></div><div class="barista"></div>',
	'<div class="lanes l1"></div><div class="car c1"></div>',
	'<div class="lanes l1"></div><div class="lanes l2"></div><div class="car c2"></div><div class="barista"></div>',
);
?>
<section class="sec wrap" id="videos" style="padding-top:0">
	<div class="sec-head rv" style="flex-direction:row;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:20px">
		<div style="display:flex;flex-direction:column;gap:18px"><span class="eyebrow">Videos</span><h2>Nos vieron en el taco.<br>Y en TikTok.</h2></div>
		<?php if ( $fc_ig ) : ?><a class="btn ghost small" href="<?php echo esc_url( $fc_ig ); ?>" target="_blank" rel="noopener">Síguenos en Instagram ↗</a><?php endif; ?>
	</div>
	<div class="bento rv" id="bento">
		<?php
		foreach ( $fc_videos as $fc_i => $fc_v ) :
			$fc_tipos = get_the_terms( $fc_v, 'fc_tipo_video' );
			$fc_tag   = ( $fc_tipos && ! is_wp_error( $fc_tipos ) ) ? $fc_tipos[0]->name : '';
			$fc_mp4   = (int) fct_campo( $fc_v->ID, 'video' );
			$fc_mp4u  = $fc_mp4 ? wp_get_attachment_url( $fc_mp4 ) : '';
			$fc_url   = fct_campo( $fc_v->ID, 'url', $fc_ig );
			$fc_thumb = has_post_thumbnail( $fc_v ) ? get_the_post_thumbnail_url( $fc_v, 'fc-vertical' ) : '';
			$fc_cap   = get_the_title( $fc_v );
			?>
			<button class="tile" type="button" data-cursor="Ver" data-cap="<?php echo esc_attr( $fc_cap ); ?>" data-url="<?php echo esc_url( $fc_url ); ?>" data-video="<?php echo esc_url( $fc_mp4u ); ?>" data-poster="<?php echo esc_url( $fc_thumb ); ?>">
				<?php if ( $fc_thumb ) : ?>
					<img class="media-cover scene" src="<?php echo esc_url( $fc_thumb ); ?>" alt="" loading="lazy">
				<?php else : ?>
					<div class="scene"><?php echo $fc_escena[ $fc_i % 5 ]; // phpcs:ignore ?></div>
				<?php endif; ?>
				<span class="bar"></span>
				<span class="rec"><i></i>Ver</span>
				<span class="cap"><?php if ( $fc_tag ) : ?><span class="tag"><?php echo esc_html( $fc_tag ); ?></span><?php endif; ?><strong><?php echo esc_html( $fc_cap ); ?></strong></span>
			</button>
		<?php endforeach; ?>
	</div>
</section>
