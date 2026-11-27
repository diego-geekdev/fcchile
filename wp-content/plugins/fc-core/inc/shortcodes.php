<?php
/**
 * Shortcodes para usar dentro de las páginas:
 *
 * [fc_calculadora]                     Calculadora de recuperación del equipo.
 * [fc_escenarios]                      Tabla de escenarios (calle, eventos, oficina) con los valores de Ajustes.
 * [fc_whatsapp texto="" mensaje=""]    Botón a WhatsApp.
 * [fc_precio campo="precio_equipo"]    Un valor de Ajustes con formato $ chileno.
 * [fc_imprimir]                        Botón «Guardar como PDF».
 * [fc_brochures]                       Tarjetas de los brochures (Calle, Eventos, Negocios).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode(
	'fc_precio',
	function ( $atts ) {
		$a = shortcode_atts( array( 'campo' => 'precio_equipo' ), $atts );
		$permitidos = array( 'precio_equipo', 'precio_vaso' );
		if ( ! in_array( $a['campo'], $permitidos, true ) ) {
			return '';
		}
		return '$' . fc_num( fc_opt_num( $a['campo'] ) );
	}
);

add_shortcode(
	'fc_whatsapp',
	function ( $atts ) {
		$a   = shortcode_atts(
			array(
				'texto'   => 'Pedir cotización por WhatsApp',
				'mensaje' => 'Hola, quiero cotizar un equipo First Coffee',
				'estilo'  => '',
			),
			$atts
		);
		$url = fc_whatsapp_url( $a['mensaje'] );
		if ( ! $url ) {
			$url = home_url( '/#cotizar' );
		}
		$clase = 'btn' . ( 'ghost' === $a['estilo'] ? ' ghost' : '' );
		return sprintf( '<a class="%s" href="%s" target="_blank" rel="noopener">%s</a>', esc_attr( $clase ), esc_url( $url ), esc_html( $a['texto'] ) );
	}
);

add_shortcode(
	'fc_imprimir',
	function ( $atts ) {
		$a = shortcode_atts( array( 'texto' => 'Guardar como PDF' ), $atts );
		return '<button type="button" class="btn ghost small fc-imprimir" onclick="window.print()">' . esc_html( $a['texto'] ) . '</button>';
	}
);

add_shortcode(
	'fc_escenarios',
	function () {
		$vasos = max( 1, (int) fc_opt_num( 'vasos_carga' ) );
		$dsem  = max( 1, fc_opt_num( 'jornadas_semana' ) );
		$filas = array(
			array( 'Calle, se vende el estanque completo', $vasos, 'jornadas', true ),
			array( 'Calle, jornada más corta', 25, 'jornadas', true ),
			array( 'Eventos y ferias (100 vasos por evento)', 100, 'eventos', false ),
			array( 'Oficina o local (20 vasos al día)', 20, 'días', true ),
		);
		$h  = '<div class="fc-tabla-wrap"><table class="fc-tabla fc-escenarios"><thead><tr><th>Escenario</th><th>Venta</th><th>Margen (' . esc_html( fc_num( fc_opt_num( 'margen' ) ) ) . '%)</th><th>Se paga en</th></tr></thead><tbody>';
		foreach ( $filas as $f ) {
			$e   = fc_escenario( $f[1], $dsem );
			$en  = fc_num( $e['dias'], 1 ) . ' ' . $f[2];
			if ( $f[3] ) {
				$en .= ' <span class="fc-sub">≈ ' . fc_num( $e['semanas'], 1 ) . ' semanas de ' . fc_num( $dsem ) . ' jornadas</span>';
			}
			$h .= sprintf(
				'<tr><td>%s<span class="fc-sub">%s vasos a $%s</span></td><td>$%s</td><td>$%s</td><td>%s</td></tr>',
				esc_html( $f[0] ),
				esc_html( fc_num( $f[1] ) ),
				esc_html( fc_num( fc_opt_num( 'precio_vaso' ) ) ),
				esc_html( fc_num( $e['venta'] ) ),
				esc_html( fc_num( $e['margen'] ) ),
				wp_kses( $en, array( 'span' => array( 'class' => true ) ) )
			);
		}
		$h .= '</tbody></table></div>';
		$h .= '<p class="fc-nota">Escenarios ilustrativos con un equipo de $' . esc_html( fc_num( fc_opt_num( 'precio_equipo' ) ) ) . '. Las ventas dependen del lugar y la hora, y el margen, de los insumos. No son una promesa de venta.</p>';
		return $h;
	}
);

add_shortcode(
	'fc_calculadora',
	function () {
		$precio = fc_opt_num( 'precio_equipo' );
		$vasos  = fc_opt_num( 'vasos_carga' );
		$pv     = fc_opt_num( 'precio_vaso' );
		$mg     = fc_opt_num( 'margen' );
		$ds     = fc_opt_num( 'jornadas_semana' );
		$campos = array(
			array( 'equipo', 'Precio del equipo', '$', $precio, 1000 ),
			array( 'vasos', 'Vasos por jornada', 'vasos', $vasos, 1 ),
			array( 'pvaso', 'Precio por vaso', '$', $pv, 10 ),
			array( 'margen', 'Margen sobre la venta', '%', $mg, 1 ),
			array( 'dias', 'Jornadas de venta por semana', 'días', $ds, 1 ),
		);
		$id = 'fccalc' . wp_rand( 100, 999 );
		ob_start();
		?>
		<div class="fc-calc" id="<?php echo esc_attr( $id ); ?>">
			<div class="fc-calc-campos">
				<?php foreach ( $campos as $c ) : ?>
					<label class="fc-calc-campo">
						<span><?php echo esc_html( $c[1] ); ?></span>
						<span class="fc-calc-input">
							<?php if ( '$' === $c[2] ) : ?><em>$</em><?php endif; ?>
							<input type="number" inputmode="numeric" min="0" step="<?php echo esc_attr( $c[4] ); ?>" data-k="<?php echo esc_attr( $c[0] ); ?>" value="<?php echo esc_attr( $c[3] ); ?>">
							<?php if ( '$' !== $c[2] ) : ?><em><?php echo esc_html( $c[2] ); ?></em><?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
			<div class="fc-calc-res" aria-live="polite">
				<div class="fc-calc-main"><span>Se paga en</span><b data-o="recupera">—</b><small data-o="semanas"></small></div>
				<div><span>Venta por jornada</span><b data-o="venta">—</b></div>
				<div><span>Margen mensual estimado</span><b data-o="mensual">—</b></div>
			</div>
			<p class="fc-nota">Días para recuperar = precio del equipo ÷ margen de cada jornada. Mes de 4,33 semanas. Valores referenciales.</p>
		</div>
		<script>
		(function(){var r=document.getElementById('<?php echo esc_js( $id ); ?>');if(!r)return;
			var f=function(n,d){return Number(n).toLocaleString('es-CL',{minimumFractionDigits:d||0,maximumFractionDigits:d||0})};
			function calc(){var v={};r.querySelectorAll('input[data-k]').forEach(function(i){v[i.dataset.k]=Math.max(0,parseFloat(i.value)||0)});
				var venta=v.vasos*v.pvaso, mj=venta*v.margen/100, d=mj>0?v.equipo/mj:0, s=v.dias>0?d/v.dias:0, mes=mj*v.dias*4.33;
				r.querySelector('[data-o=venta]').textContent='$'+f(venta);
				r.querySelector('[data-o=recupera]').textContent=mj>0?f(d,1)+' jornadas':'—';
				r.querySelector('[data-o=semanas]').textContent=mj>0&&v.dias>0?'≈ '+f(s,1)+' semanas':'';
				r.querySelector('[data-o=mensual]').textContent='$'+f(mes);}
			r.addEventListener('input',calc);calc();})();
		</script>
		<?php
		return ob_get_clean();
	}
);

add_shortcode(
	'fc_brochures',
	function () {
		$items = array(
			array( 'brochures/calle', 'Calle', 'Tu punto de café, en la espalda', 'Paraderos, metro, oficinas y ferias.', 'Se paga en ≈ 10,5 jornadas' ),
			array( 'brochures/eventos', 'Eventos', 'Café caliente para tus invitados, sin enchufes', 'Matrimonios, ferias, deporte y eventos de empresa.', 'Se paga en ≈ 3,7 eventos' ),
			array( 'brochures/negocios', 'Negocios', 'Café en cada turno, sin cafetera industrial', 'Oficinas, locales, clínicas y obras.', 'Se paga en ≈ 18 días' ),
		);
		$h = '<div class="fc-tarjetas">';
		foreach ( $items as $it ) {
			$pg = get_page_by_path( $it[0] );
			if ( ! $pg ) {
				continue;
			}
			$h .= sprintf(
				'<a class="fc-tarjeta" href="%s"><span class="fc-eti">%s</span><span class="fc-tarjeta-t">%s</span><span class="fc-tarjeta-d">%s</span><b>%s</b></a>',
				esc_url( get_permalink( $pg ) ),
				esc_html( $it[1] ),
				esc_html( $it[2] ),
				esc_html( $it[3] ),
				esc_html( $it[4] )
			);
		}
		return $h . '</div>';
	}
);
