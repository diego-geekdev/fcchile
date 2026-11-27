<?php
/**
 * «Por qué»: beneficios del equipo (First Coffee → Beneficios).
 */
$fc_ben = fct_items( 'fc_beneficio' );
if ( ! $fc_ben ) {
	return;
}
?>
<section class="sec wrap" id="por-que">
	<div class="sec-head rv"><span class="eyebrow">Por qué First Coffee</span><h2>Lo que cambia<br>en tu jornada.</h2></div>
	<div class="ben-grid rv">
		<?php foreach ( $fc_ben as $fc_i => $fc_b ) : ?>
			<article class="ben">
				<span class="ben-ico"><?php echo fct_icono( fct_campo( $fc_b->ID, 'icono', 'listo' ) ); // phpcs:ignore ?></span>
				<span class="ben-num"><?php echo esc_html( str_pad( (string) ( $fc_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<h3><?php echo esc_html( get_the_title( $fc_b ) ); ?></h3>
				<p><?php echo esc_html( wp_strip_all_tags( $fc_b->post_content ) ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
</section>
