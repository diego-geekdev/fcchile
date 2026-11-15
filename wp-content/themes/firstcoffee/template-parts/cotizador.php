<?php
/**
 * Cotizador en 4 pasos. Envía a /wp-json/fc/v1/cotizacion (plugin fc-core).
 */
$fc_kits   = function_exists( 'fc_opciones_kits' ) ? fc_opciones_kits() : array();
$fc_origen = function_exists( 'fc_opciones_origen' ) ? fc_opciones_origen() : array();
if ( ! $fc_kits ) {
	return; // Sin el plugin no hay dónde guardar la cotización.
}
$fc_plazo = fct_opt( 'plazo_respuesta', 'a la brevedad' );
?>
<section class="sec wrap" id="cotizar">
	<div class="sec-head rv"><span class="eyebrow">Cotiza</span><h2>Tu cotización en 4 pasos.</h2><p class="lead">Una pregunta a la vez. Te toma menos de un minuto y te respondemos por WhatsApp o correo.</p></div>
	<div class="quote">
		<form class="qcard" id="qcard" novalidate>
			<div class="resume" id="resume" hidden><span>Retomamos tu cotización donde la dejaste.</span><button class="linkbtn" type="button" id="restart">Empezar de nuevo</button></div>
			<div class="qroad" aria-hidden="true"><span class="fill" id="qfill"></span><span class="qcar" id="qcar"></span></div>
			<div class="qstep-label"><span id="qLabel">Paso 1 de 4</span><span>Sin compromiso</span></div>

			<!-- Campo trampa para bots: las personas no lo ven. -->
			<div class="sr" aria-hidden="true"><label for="q-web">No completar</label><input id="q-web" name="sitio_web" type="text" tabindex="-1" autocomplete="off"></div>

			<div class="qstep" data-step="1">
				<h3 tabindex="-1">¿Cuántos kits necesitas?</h3>
				<div class="opts" role="group" aria-label="Cantidad de kits">
					<?php foreach ( $fc_kits as $fc_k => $fc_t ) : ?>
						<button class="opt" type="button" data-qty="<?php echo esc_attr( $fc_k ); ?>" aria-pressed="false"><b><?php echo esc_html( $fc_t[0] ); ?></b><small><?php echo esc_html( $fc_t[1] ); ?></small></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="qstep" data-step="2" hidden>
				<h3 tabindex="-1">¿Le ponemos tu logo?</h3>
				<div class="opts two" role="group" aria-label="Personalización">
					<button class="opt" type="button" data-logo="si" aria-pressed="false"><b>Sí, con mi logo</b></button>
					<button class="opt" type="button" data-logo="no" aria-pressed="false"><b>Sin logo</b></button>
				</div>
				<div id="logoArea" hidden style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:center">
					<label class="drop" id="drop2" for="logoFile2" data-cursor="Subir"><strong id="drop2Text">Arrastra tu logo o toca para elegirlo</strong><small>PNG, JPG o PDF · hasta 10 MB · también puedes mandarlo después</small></label>
					<div class="mini-pack" aria-hidden="true">
						<svg viewBox="0 0 200 260"><rect x="30" y="38" width="140" height="210" rx="30" class="f-pack"/><rect x="40" y="38" width="120" height="78" rx="24" class="f-pack2"/><rect x="52" y="160" width="96" height="72" rx="16" class="f-pack2"/><rect x="70" y="86" width="60" height="58" rx="10" class="s-slot"/></svg>
						<div class="logo-on" id="logoOn2"></div>
					</div>
				</div>
				<input class="sr" type="file" id="logoFile2" accept=".png,.jpg,.jpeg,.webp,.pdf,image/png,image/jpeg,image/webp,application/pdf">
				<p class="err" id="logoErr2" role="alert"></p>
				<div class="field"><label for="q-comments">Comentarios <span class="note">(opcional)</span></label><textarea id="q-comments" maxlength="2000" placeholder="Por ejemplo: colores de mi marca, fecha en que lo necesito, ciudad donde voy a vender"></textarea></div>
				<div class="qnav"><button class="btn ghost small" type="button" data-back>Volver</button><button class="btn small" type="button" data-next="2">Continuar</button></div>
			</div>

			<div class="qstep" data-step="3" hidden>
				<h3 tabindex="-1">¿Cómo te contactamos?</h3>
				<div class="fields">
					<div class="field" data-f="name"><label for="q-name">Nombre</label><input id="q-name" autocomplete="name"><p class="err" id="e-name"></p></div>
					<div class="field" data-f="phone"><label for="q-phone">WhatsApp</label><input id="q-phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+56 9 1234 5678"><p class="err" id="e-phone"></p></div>
					<div class="field" data-f="email"><label for="q-email">Correo</label><input id="q-email" type="email" inputmode="email" autocomplete="email" placeholder="tu@correo.cl"><p class="err" id="e-email"></p></div>
					<div class="field" data-f="comuna"><label for="q-comuna">Comuna o ciudad</label><input id="q-comuna" autocomplete="address-level2"><p class="err" id="e-comuna"></p></div>
				</div>
				<div style="display:flex;flex-direction:column;gap:10px"><span style="font-weight:600;font-size:15px">¿Cómo nos conociste? <span class="note">(opcional)</span></span>
					<div class="chips" role="group" aria-label="Cómo nos conociste">
						<?php foreach ( $fc_origen as $fc_o ) : ?>
							<button class="chip" type="button" data-src="<?php echo esc_attr( $fc_o ); ?>" aria-pressed="false"><?php echo esc_html( $fc_o ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="qnav"><button class="btn ghost small" type="button" data-back>Volver</button><button class="btn small" type="button" data-next="3">Revisar</button></div>
			</div>

			<div class="qstep" data-step="4" hidden>
				<h3 tabindex="-1">Revisa y envía</h3>
				<dl class="review" id="review"></dl>
				<p class="err" id="sendErr" role="alert"></p>
				<div class="qnav"><button class="btn ghost small" type="button" data-back>Volver</button><button class="btn magnet" type="button" id="send" data-cursor="Enviar">Enviar cotización</button></div>
			</div>

			<div class="qstep done" data-step="5" hidden id="done">
				<div class="cupwrap"><div class="cup"><i></i></div></div>
				<h3 tabindex="-1">¡Listo, recibimos tu solicitud!</h3>
				<p class="lead">Te respondemos <?php echo esc_html( $fc_plazo ); ?> por WhatsApp o correo. También te enviamos una copia a tu correo.</p>
				<p class="note" id="doneNum"></p>
				<div class="qnav"><button class="btn ghost small" type="button" id="again">Hacer otra cotización</button></div>
			</div>
		</form>

		<aside class="qside" aria-label="Resumen de tu cotización">
			<h4>Tu cotización</h4>
			<dl class="sum">
				<div><dt>Kits</dt><dd id="sQty" class="empty">Sin elegir</dd></div>
				<div><dt>Logo</dt><dd id="sLogo" class="empty">Sin elegir</dd></div>
				<div><dt>Comentarios</dt><dd id="sCom" class="empty">Sin comentarios</dd></div>
				<div><dt>Contacto</dt><dd id="sCon" class="empty">Falta completar</dd></div>
			</dl>
			<p class="note">Se llena mientras avanzas. Si cierras la página, guardamos lo avanzado en este navegador (menos el archivo del logo).</p>
		</aside>
	</div>
</section>
