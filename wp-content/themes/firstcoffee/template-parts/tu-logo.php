<?php
/**
 * «¿Cómo se vería con tu logo?»: vista previa en la mochila. El archivo se comparte con el cotizador.
 */
?>
<section class="sec" id="logo" style="background:var(--asphalt-2);border-block:1px solid var(--line)">
	<div class="wrap logo-grid">
		<div class="pack-view" id="packView">
			<svg viewBox="0 0 200 260" aria-hidden="true"><path d="M60 34c0-20 80-20 80 0" class="s-line" stroke-width="8"/><rect x="30" y="38" width="140" height="210" rx="30" class="f-pack"/><rect x="40" y="38" width="120" height="78" rx="24" class="f-pack2"/><rect x="52" y="160" width="96" height="72" rx="16" class="f-pack2"/><path d="M62 186h76" class="s-line"/><rect x="70" y="86" width="60" height="58" rx="10" class="s-slot"/><rect x="18" y="80" width="14" height="120" rx="7" class="f-pack2"/><rect x="168" y="80" width="14" height="120" rx="7" class="f-pack2"/></svg>
			<div class="logo-on" id="logoOn"><span class="ph">Tu logo<br>aquí</span></div>
		</div>
		<div style="display:flex;flex-direction:column;gap:20px;min-width:0">
			<span class="eyebrow">Tu marca</span>
			<h2>¿Cómo se vería con tu logo?</h2>
			<p class="lead">Sube tu logo y míralo en la mochila. Si te gusta, lo llevamos directo a tu cotización.</p>
			<label class="drop" id="drop1" for="logoFile1" data-cursor="Subir">
				<strong id="drop1Text">Arrastra tu logo aquí o toca para elegirlo</strong>
				<small>PNG, JPG o PDF · hasta 10 MB</small>
			</label>
			<input class="sr" type="file" id="logoFile1" accept=".png,.jpg,.jpeg,.webp,.pdf,image/png,image/jpeg,image/webp,application/pdf">
			<p class="err" id="logoErr1" role="alert"></p>
			<div style="display:flex;flex-direction:column;gap:8px">
				<span style="font-size:13px;color:var(--muted)">Color de la mochila</span>
				<div class="swatches" role="group" aria-label="Color de la mochila">
					<button class="sw" type="button" style="--c:#262626" data-color="#262626" aria-pressed="true" aria-label="Negro"></button>
					<button class="sw" type="button" style="--c:#7b7d84" data-color="#7b7d84" aria-pressed="false" aria-label="Gris"></button>
					<button class="sw" type="button" style="--c:#69452F" data-color="#69452F" aria-pressed="false" aria-label="Café"></button>
				</div>
			</div>
			<div style="display:flex;gap:12px;flex-wrap:wrap">
				<button class="btn magnet" type="button" id="logoToQuote" data-cursor="Cotizar">Me gusta, cotizar</button>
				<button class="btn ghost" type="button" id="logoClear" hidden>Quitar logo</button>
			</div>
		</div>
	</div>
</section>
