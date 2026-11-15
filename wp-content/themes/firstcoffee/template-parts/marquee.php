<?php
/**
 * Cinta en movimiento con frases (Ajustes → Frases de la cinta).
 */
$fc_frases = array_values( array_filter( array_map( 'trim', explode( "\n", (string) fct_opt( 'marquee' ) ) ) ) );
if ( ! $fc_frases ) {
	return;
}
$fc_serie = '';
foreach ( $fc_frases as $fc_f ) {
	$fc_serie .= '<span class="mq-item">' . esc_html( $fc_f ) . '</span><span class="mq-dot">●</span>';
}
// Se repite la serie hasta llenar el ancho y se duplica para el bucle continuo.
$fc_rep = max( 2, (int) ceil( 8 / count( $fc_frases ) ) );
?>
<div class="marquee" id="marquee" aria-label="<?php echo esc_attr( implode( ', ', $fc_frases ) ); ?>">
	<div class="mq-track" id="mqTrack" aria-hidden="true">
		<?php echo str_repeat( $fc_serie, $fc_rep * 2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</div>
