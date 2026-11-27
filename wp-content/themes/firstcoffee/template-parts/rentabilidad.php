<?php
/**
 * «¿En cuántos días se paga?»: cifras calculadas con los valores de Ajustes + calculadora.
 */
if ( ! function_exists( 'fc_escenario' ) ) {
	return;
}
$fc_vasos = max( 1, (int) fc_opt_num( 'vasos_carga' ) );
$fc_e     = fc_escenario( $fc_vasos );
$fc_dsem  = max( 1, fc_opt_num( 'jornadas_semana' ) );
$fc_cifras = array(
	array( fc_num( $fc_vasos ), 'vasos por jornada', $fc_vasos, '' ),
	array( '$' . fc_num( fc_opt_num( 'precio_vaso' ) ), 'por vaso', (int) fc_opt_num( 'precio_vaso' ), 'money' ),
	array( fc_num( fc_opt_num( 'margen' ) ) . '%', 'de margen', null, '' ),
	array( '≈ ' . fc_num( $fc_e['semanas'], 1 ), 'semanas para pagar el equipo', null, '' ),
);
?>
<section class="sec wrap" id="rentabilidad">
	<div class="renta rv">
		<div class="renta-head">
			<span class="eyebrow">Rentabilidad</span>
			<h2>¿En cuántos días se paga?</h2>
			<p class="lead">Vendiendo un estanque al día (<?php echo esc_html( fc_num( $fc_vasos ) ); ?> vasos a $<?php echo esc_html( fc_num( fc_opt_num( 'precio_vaso' ) ) ); ?>) con <?php echo esc_html( fc_num( fc_opt_num( 'margen' ) ) ); ?>% de margen, el equipo se paga en unas <strong><?php echo esc_html( fc_num( $fc_e['dias'], 1 ) ); ?> jornadas</strong>: cerca de <?php echo esc_html( fc_num( round( $fc_e['semanas'] ) ) ); ?> semanas de trabajo.</p>
			<div class="renta-ctas">
				<a class="btn" href="<?php echo esc_url( fct_pagina_url( 'ficha', '#escenarios' ) ); ?>">Ver escenarios</a>
				<a class="btn ghost" href="#cotizar">Cotizar mi equipo</a>
			</div>
		</div>
		<div class="stats renta-stats" style="--n:4">
			<?php foreach ( $fc_cifras as $fc_c ) : ?>
				<div class="stat">
					<?php if ( null !== $fc_c[2] ) : ?>
						<b data-count="<?php echo (int) $fc_c[2]; ?>" data-fmt="<?php echo esc_attr( $fc_c[3] ); ?>"><?php echo esc_html( $fc_c[0] ); ?></b>
					<?php else : ?>
						<b><?php echo esc_html( $fc_c[0] ); ?></b>
					<?php endif; ?>
					<span><?php echo esc_html( $fc_c[1] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="renta-calc">
			<h3>Calcula con tus números</h3>
			<?php echo do_shortcode( '[fc_calculadora]' ); // phpcs:ignore ?>
		</div>
	</div>
</section>
