<?php
/**
 * Cierre «Parte con tu equipo First Coffee»: cotizador en 4 pasos + WhatsApp.
 * Envía a /wp-json/fc/v1/cotizacion (plugin fc-core).
 */
$fc_usos   = function_exists( 'fc_opciones_uso' ) ? fc_opciones_uso() : array();
$fc_kits   = function_exists( 'fc_opciones_kits' ) ? fc_opciones_kits() : array();
$fc_origen = function_exists( 'fc_opciones_origen' ) ? fc_opciones_origen() : array();
if ( ! $fc_kits || ! $fc_usos ) {
	return; // Sin el plugin no hay dónde guardar la cotización.
}
$fc_plazo = fct_opt( 'plazo_respuesta', 'a la brevedad' );
$fc_wa    = fct_whatsapp( 'Hola, quiero cotizar un equipo First Coffee' );
?>
<section class="sec wrap" id="cotizar">
	<div class="sec-head rv">
		<span class="eyebrow">Cotiza</span>
		<h2>Parte con tu equipo<br>First Coffee.</h2>
		<p class="lead">Cuéntanos dónde piensas vender y te enviamos precio, plazo de entrega y forma de compra. Te toma menos de un minuto.</p>
		<?php if ( '#cotizar' !== $fc_wa ) : ?>
			<p class="cot-alt">¿Prefieres conversar? <a href="<?php echo esc_url( $fc_wa ); ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp ↗</a></p>
		<?php endif; ?>
	</div>
	<div class="quote">
		<form class="qcard" id="qcard" novalidate>
			<div class="resume" id="resume" hidden><span>Retomamos tu cotización donde la dejaste.</span><button class="linkbtn" type="button" id="restart">Empezar de nuevo</button></div>
			<div class="qroad" aria-hidden="true"><span class="fill" id="qfill"></span><span class="qcar" id="qcar"></span></div>
			<div class="qstep-label"><span id="qLabel">Paso 1 de 4</span><span>Sin compromiso</span></div>

			<!-- Campo trampa para bots: las personas no lo ven. -->
			<div class="sr" aria-hidden="true"><label for="q-web">No completar</label><input id="q-web" name="sitio_web" type="text" tabindex="-1" autocomplete="off"></div>

			<div class="qstep" data-step="1">
				<h3 tabindex="-1">¿Dónde vas a vender?</h3>
				<div class="opts" role="group" aria-label="Dónde vas a vender">
					<?php foreach ( $fc_usos as $fc_k => $fc_t ) : ?>
						<button class="opt" type="button" data-uso="<?php echo esc_attr( $fc_k ); ?>" aria-pressed="false"><b><?php echo esc_html( $fc_t[0] ); ?></b><small><?php echo esc_html( $fc_t[1] ); ?></small></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="qstep" data-step="2" hidden>
				<h3 tabindex="-1">¿Cuántos equipos necesitas?</h3>
				<div class="opts" role="group" aria-label="Cantidad de equipos">
					<?php foreach ( $fc_kits as $fc_k => $fc_t ) : ?>
						<button class="opt" type="button" data-qty="<?php echo esc_attr( $fc_k ); ?>" aria-pressed="false"><b><?php echo esc_html( $fc_t[0] ); ?></b><small><?php echo esc_html( $fc_t[1] ); ?></small></button>
					<?php endforeach; ?>
				</div>
				<p class="err" id="qtyErr" role="alert"></p>
				<div class="field"><label for="q-comments">Comentarios <span class="note">(opcional)</span></label><textarea id="q-comments" maxlength="2000" placeholder="Por ejemplo: comuna o ciudad donde vas a vender, fecha en que lo necesitas, si es para un evento"></textarea></div>
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
				<p class="lead">Te respondemos <?php echo esc_html( $fc_plazo ); ?> por WhatsApp o correo con precio, plazo de entrega y forma de compra. También te enviamos una copia a tu correo.</p>
				<p class="note" id="doneNum"></p>
				<div class="qnav"><button class="btn ghost small" type="button" id="again">Hacer otra cotización</button></div>
			</div>
		</form>

		<aside class="qside" aria-label="Resumen de tu cotización">
			<h4>Tu cotización</h4>
			<dl class="sum">
				<div><dt>Dónde vas a vender</dt><dd id="sUso" class="empty">Sin elegir</dd></div>
				<div><dt>Equipos</dt><dd id="sQty" class="empty">Sin elegir</dd></div>
				<div><dt>Comentarios</dt><dd id="sCom" class="empty">Sin comentarios</dd></div>
				<div><dt>Contacto</dt><dd id="sCon" class="empty">Falta completar</dd></div>
			</dl>
			<p class="note">Valores referenciales en pesos chilenos; el precio final se confirma en la cotización.</p>
		</aside>
	</div>
</section>
