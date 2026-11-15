<?php
/**
 * Pie de página, botón de WhatsApp, barra móvil, visor de videos y cursor.
 */
$fc_home   = is_front_page() ? '' : home_url( '/' );
$fc_ig     = fct_opt( 'instagram', 'https://www.instagram.com/firstcoffeechile/' );
$fc_tt     = fct_opt( 'tiktok' );
$fc_mail   = fct_opt( 'correo_publico' );
$fc_wa_txt = fct_opt( 'whatsapp' );
$fc_wa     = fct_whatsapp();
$fc_trab   = fct_opt( 'trabaja_texto' );
$fc_letras = array( 'FIRST', 'COFFEE' );
?>
<footer>
	<div class="wrap">
		<div class="foot-top">
			<div class="foot-brand">
				<img class="foot-logo" src="<?php echo esc_url( fct_img( 'first-coffee-logo-blanco.png' ) ); ?>" alt="First Coffee" width="548" height="458" loading="lazy">
				<p>Café de grano en la calle,<br>directo a tu ventanilla.</p>
			</div>
			<nav class="foot-cols" aria-label="Pie de página">
				<div><h4>Menú</h4>
					<a href="<?php echo esc_url( $fc_home . '#como' ); ?>">Cómo funciona</a>
					<a href="<?php echo esc_url( $fc_home . '#kit' ); ?>">El kit</a>
					<a href="<?php echo esc_url( $fc_home . '#videos' ); ?>">Videos</a>
					<a href="<?php echo esc_url( $fc_home . '#cotizar' ); ?>">Cotizar</a>
				</div>
				<?php if ( $fc_ig || $fc_tt ) : ?>
				<div><h4>Redes</h4>
					<?php if ( $fc_ig ) : ?><a href="<?php echo esc_url( $fc_ig ); ?>" target="_blank" rel="noopener">Instagram ↗</a><?php endif; ?>
					<?php if ( $fc_tt ) : ?><a href="<?php echo esc_url( $fc_tt ); ?>" target="_blank" rel="noopener">TikTok ↗</a><?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if ( $fc_mail || $fc_wa_txt ) : ?>
				<div><h4>Escríbenos</h4>
					<?php if ( $fc_wa_txt ) : ?><a href="<?php echo esc_url( $fc_wa ); ?>" target="_blank" rel="noopener">WhatsApp<br><span class="foot-dato"><?php echo esc_html( $fc_wa_txt ); ?></span></a><?php endif; ?>
					<?php if ( $fc_mail ) : ?><a href="mailto:<?php echo esc_attr( $fc_mail ); ?>">Correo<br><span class="foot-dato"><?php echo esc_html( $fc_mail ); ?></span></a><?php endif; ?>
				</div>
				<?php endif; ?>
				<?php if ( $fc_trab ) : ?>
				<div><h4>Trabaja con nosotros</h4><p><?php echo esc_html( $fc_trab ); ?></p></div>
				<?php endif; ?>
			</nav>
		</div>
		<div class="wordmark pending-ok" id="wordmark" aria-label="First Coffee">
			<?php
			$fc_i = 0;
			foreach ( $fc_letras as $fc_palabra ) {
				echo '<span class="w" aria-hidden="true">';
				foreach ( str_split( $fc_palabra ) as $fc_l ) {
					printf( '<span class="l" style="--i:%d">%s</span>', (int) $fc_i++, esc_html( $fc_l ) );
				}
				echo '</span>';
			}
			?>
		</div>
		<script>
		(function(){var w=document.getElementById('wordmark');if(!w)return;
			function fit(){var ws=w.querySelectorAll('.w');if(!ws.length)return;w.style.fontSize='100px';
				var a=ws[0].getBoundingClientRect().left,b=ws[ws.length-1].getBoundingClientRect().right,n=b-a,c=w.clientWidth;
				if(n>0&&c>0)w.style.fontSize=Math.min(260,Math.floor(100*c/n*0.995))+'px';}
			fit();addEventListener('resize',fit);if(document.fonts&&document.fonts.ready)document.fonts.ready.then(fit);})();
		</script>
		<div class="foot-bottom">
			<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> First Coffee Chile · Santiago</span>
			<a class="foot-up" href="#top">Volver arriba ↑</a>
			<span>Sitio por <a href="https://geekdev.cl" target="_blank" rel="noopener">GeekDev</a></span>
		</div>
	</div>
</footer>

<a id="wa" href="<?php echo esc_url( $fc_wa ); ?>" <?php echo '#cotizar' === $fc_wa ? '' : 'target="_blank" rel="noopener"'; ?> aria-label="Escríbenos por WhatsApp" data-cursor="Chat"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8.9c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.6 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3Z"/></svg><span>¿Dudas? Escríbenos</span></a>

<?php if ( is_front_page() ) : ?>
<div id="mbar">
	<a class="btn" href="#cotizar">Cotizar mi kit</a>
	<a class="wa-m" href="<?php echo esc_url( $fc_wa ); ?>" <?php echo '#cotizar' === $fc_wa ? '' : 'target="_blank" rel="noopener"'; ?> aria-label="WhatsApp"><svg width="24" height="24" viewBox="0 0 24 24" fill="#062210" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Z"/></svg></a>
</div>

<dialog id="lightbox" aria-label="Video">
	<div class="lb">
		<div class="lb-media" id="lbMedia">
			<div class="scene"><div class="lanes l1"></div><div class="lanes l2"></div><div class="car c1"></div><div class="car c4"></div><div class="barista"></div></div>
			<form method="dialog"><button class="lb-close" aria-label="Cerrar video"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button></form>
		</div>
		<div class="lb-body"><strong id="lbCap" style="font-weight:800;font-size:26px;line-height:1.05;letter-spacing:-.02em"></strong><a id="lbLink" href="<?php echo esc_url( $fc_ig ); ?>" target="_blank" rel="noopener">Ver la publicación ↗</a></div>
	</div>
</dialog>

<div id="cursor" aria-hidden="true"><span class="bean"></span><span class="lab" id="cursorLab"></span></div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
