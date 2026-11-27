<?php
/**
 * «El kit»: ilustración que se desarma con el scroll y fichas de cada pieza (First Coffee → Piezas del kit).
 */
$fc_piezas = fct_piezas();
$fc_orden  = array( 'mochila', 'termo', 'piezas' );
$fc_lbl    = function ( $slot, $defecto ) use ( $fc_piezas ) {
	return isset( $fc_piezas[ $slot ] ) ? $fc_piezas[ $slot ]['label'] : $defecto;
};
?>
<section class="kit" id="kit">
	<div class="kit-sticky">
		<div class="wrap kit-grid">
			<div class="kit-copy">
				<span class="eyebrow">El equipo</span>
				<h2>Todo lo que va en tu espalda.</h2>
				<p class="lead"><?php echo esc_html( fct_opt( 'kit_bajada' ) ); ?></p>
				<?php if ( $fc_piezas ) : ?>
					<div class="chips" role="group" aria-label="Ver un componente">
						<?php foreach ( $fc_orden as $fc_s ) : ?>
							<?php if ( isset( $fc_piezas[ $fc_s ] ) ) : ?>
								<button class="chip" type="button" data-piece="<?php echo esc_attr( $fc_s ); ?>" aria-pressed="false"><?php echo esc_html( $fc_piezas[ $fc_s ]['t'] ); ?></button>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="detail" id="detail" aria-live="polite">
						<h4 id="dTitle">Toca un componente para ver su detalle</h4>
						<p id="dText">Cada componente tiene su ficha. Toca un punto de la ilustración o un botón.</p>
						<ul id="dSpecs" hidden></ul>
					</div>
				<?php endif; ?>
				<div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
					<a class="btn magnet" href="<?php echo esc_url( fct_pagina_url( 'ficha' ) ); ?>" data-cursor="Ver">Ver ficha técnica</a>
					<span class="kit-hint" id="kitHint">Baja para verlo desarmado</span>
				</div>
			</div>
			<div class="stage" id="stage">
				<div class="piece p-termo" data-piece="termo">
					<svg viewBox="0 0 120 240" aria-hidden="true"><rect x="35" y="12" width="50" height="34" rx="9" class="f-steel2"/><rect x="25" y="40" width="70" height="186" rx="18" class="f-steel"/><rect x="25" y="92" width="70" height="18" class="f-paint"/><rect x="40" y="56" width="8" height="150" rx="4" fill="#ffffff" opacity=".35"/><rect x="4" y="176" width="24" height="13" rx="4" class="f-steel2"/><rect x="7" y="189" width="9" height="16" rx="3" class="f-steel2"/></svg>
					<?php if ( isset( $fc_piezas['termo'] ) ) : ?><button class="hot" type="button" data-piece="termo" aria-label="Ver detalle: <?php echo esc_attr( $fc_piezas['termo']['t'] ); ?>"></button><?php endif; ?>
					<span class="plabel"><?php echo esc_html( $fc_lbl( 'termo', 'Estanque 10 L' ) ); ?></span>
				</div>
				<div class="piece p-pack" data-piece="mochila">
					<svg viewBox="0 0 200 260" aria-hidden="true"><path d="M60 34c0-20 80-20 80 0" class="s-line" stroke-width="8"/><rect x="30" y="38" width="140" height="210" rx="30" class="f-pack"/><rect x="40" y="38" width="120" height="78" rx="24" class="f-pack2"/><rect x="52" y="160" width="96" height="72" rx="16" class="f-pack2"/><path d="M62 186h76" class="s-line"/><circle cx="100" cy="116" r="28" class="s-slot"/><rect x="18" y="80" width="14" height="120" rx="7" class="f-pack2"/><rect x="168" y="80" width="14" height="120" rx="7" class="f-pack2"/></svg>
					<?php if ( isset( $fc_piezas['mochila'] ) ) : ?><button class="hot" type="button" data-piece="mochila" aria-label="Ver detalle: <?php echo esc_attr( $fc_piezas['mochila']['t'] ); ?>"></button><?php endif; ?>
					<span class="plabel"><?php echo esc_html( $fc_lbl( 'mochila', 'Mochila térmica' ) ); ?></span>
				</div>
				<div class="piece p-piezas" data-piece="piezas">
					<svg viewBox="0 0 160 150" aria-hidden="true"><path d="M20 40h50l-7 90H27z" class="f-cream"/><path d="M28 30h50l-7 90H35z" class="f-cream" opacity=".75"/><rect x="16" y="32" width="58" height="10" rx="5" class="f-paint"/><ellipse cx="118" cy="56" rx="30" ry="9" class="f-steel"/><rect x="88" y="56" width="60" height="10" class="f-steel2"/><path d="M96 96c22-14 44 14 22 26s-30 16-6 22" fill="none" stroke="var(--steel)" stroke-width="7" stroke-linecap="round"/></svg>
					<?php if ( isset( $fc_piezas['piezas'] ) ) : ?><button class="hot" type="button" data-piece="piezas" aria-label="Ver detalle: <?php echo esc_attr( $fc_piezas['piezas']['t'] ); ?>"></button><?php endif; ?>
					<span class="plabel"><?php echo esc_html( $fc_lbl( 'piezas', 'Grifo y bomba' ) ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>
