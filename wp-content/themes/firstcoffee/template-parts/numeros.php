<?php
/**
 * «Lo que llevamos recorrido»: hasta 4 cifras de Ajustes. Se oculta si no hay ninguna.
 */
$fc_stats = array();
for ( $fc_n = 1; $fc_n <= 4; $fc_n++ ) {
	$fc_v = trim( (string) fct_opt( 'stat' . $fc_n . '_valor' ) );
	if ( '' !== $fc_v ) {
		$fc_stats[] = array( $fc_v, fct_opt( 'stat' . $fc_n . '_texto' ) );
	}
}
if ( ! $fc_stats ) {
	return;
}
?>
<section class="sec wrap" aria-labelledby="statsTitle">
	<div class="sec-head rv"><span class="eyebrow" id="statsTitle">Lo que llevamos recorrido</span></div>
	<div class="stats rv" style="--n:<?php echo count( $fc_stats ); ?>">
		<?php
		foreach ( $fc_stats as $fc_s ) :
			$fc_c = fct_cifra( $fc_s[0] );
			?>
			<div class="stat">
				<?php if ( $fc_c ) : ?>
					<b data-count="<?php echo (int) $fc_c[0]; ?>" data-fmt="<?php echo esc_attr( $fc_c[1] ); ?>"><?php echo esc_html( $fc_s[0] ); ?></b>
				<?php else : ?>
					<b><?php echo esc_html( $fc_s[0] ); ?></b>
				<?php endif; ?>
				<span><?php echo esc_html( $fc_s[1] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>
