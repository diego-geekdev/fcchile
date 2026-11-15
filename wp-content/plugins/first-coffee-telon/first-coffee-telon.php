<?php
/**
 * Plugin Name: First Coffee — Telón «Estamos preparando café»
 * Description: Muestra una página de "próximamente" a los visitantes mientras el sitio está en construcción. Quienes tienen sesión iniciada como editores o administradores ven el sitio normal. Para quitar el telón, desactiva el plugin (o borra el archivo si está en wp-content/mu-plugins/).
 * Author: GeekDev — Diego Catalán
 * Version: 1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', function () {

	// Deja pasar al equipo y a los procesos internos de WordPress.
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return;
	}
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	// 503 + Retry-After: Google entiende que es temporal y no indexa el telón.
	status_header( 503 );
	header( 'Retry-After: 86400' );
	header( 'Content-Type: text/html; charset=utf-8' );
	nocache_headers();

	$instagram = 'https://www.instagram.com/firstcoffeechile/';
	?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0b0b0c">
<title>First Coffee Chile — Estamos preparando café</title>
<meta name="description" content="Café de grano en la calle. Muy pronto, nuestra web.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Stencil:opsz,wght@10..72,700..900&family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Martian+Mono:wdth,wght@75..112.5,400..600&display=swap">
<style>
	:root {
		--negro: #0b0b0c;
		--linea: #2a2a2f;
		--blanco: #f5f3ee;
		--gris: #a3a09a;
		--amarillo: #f2c230;
		--tinta: #1c1606;
		--f-display: "Big Shoulders Stencil", "Arial Narrow", Impact, sans-serif;
		--f-body: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
		--f-mono: "Martian Mono", ui-monospace, Menlo, monospace;
	}
	* { box-sizing: border-box; margin: 0; padding: 0; }
	html, body { height: 100%; }
	body {
		min-height: 100vh;
		min-height: 100svh;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: clamp(1.4rem, 3vw, 2.2rem);
		padding: 3rem clamp(1rem, 4vw, 3rem) 4.5rem;
		background: var(--negro);
		color: var(--blanco);
		font-family: var(--f-body);
		text-align: center;
		overflow-x: hidden;
		-webkit-font-smoothing: antialiased;
	}

	/* Aviso superior */
	.aviso {
		font-family: var(--f-mono);
		font-stretch: 85%;
		font-size: clamp(.72rem, 1.4vw, .85rem);
		letter-spacing: .2em;
		text-transform: uppercase;
		color: var(--amarillo);
		display: inline-flex;
		align-items: center;
		gap: .8rem;
	}
	.aviso::before, .aviso::after {
		content: "";
		width: 28px;
		height: 3px;
		background: repeating-linear-gradient(90deg, var(--amarillo) 0 8px, transparent 8px 12px);
	}

	/* Logotipo grande: letras blancas que se vuelven amarillas al pasar el mouse */
	h1.wordmark {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		column-gap: .22em;
		font-family: var(--f-display);
		font-weight: 900;
		font-optical-sizing: auto;
		font-size: clamp(4.2rem, 15vw, 14rem);
		line-height: .82;
		text-transform: uppercase;
		letter-spacing: .01em;
		margin-top: .12em;
	}
	@media (max-width: 600px) {
		h1.wordmark { font-size: 30vw; row-gap: .06em; }
	}
	.wordmark .palabra { display: inline-flex; white-space: nowrap; }
	.wordmark .l {
		display: inline-block;
		color: var(--blanco);
		transition: color .25s ease, transform .25s ease;
		cursor: default;
	}
	.wordmark .l:hover,
	.wordmark .l.on {
		color: var(--amarillo);
		transform: translateY(-.04em);
	}

	p.bajada {
		max-width: 32rem;
		font-size: clamp(1rem, 1.6vw, 1.12rem);
		line-height: 1.6;
		color: var(--gris);
	}

	a.boton {
		display: inline-flex;
		align-items: center;
		gap: .6rem;
		min-height: 52px;
		padding: 0 1.6rem;
		border: 2px solid var(--amarillo);
		border-radius: 999px;
		background: var(--amarillo);
		color: var(--tinta);
		text-decoration: none;
		font-size: 1rem;
		font-weight: 800;
		position: relative;
		overflow: hidden;
		isolation: isolate;
	}
	a.boton::before {
		content: "";
		position: absolute;
		inset: 0;
		background: var(--blanco);
		transform: translateY(101%);
		transition: transform .45s cubic-bezier(.2, .8, .2, 1);
		z-index: -1;
	}
	a.boton:hover::before, a.boton:focus-visible::before { transform: translateY(0); }
	a.boton:focus-visible { outline: 3px solid var(--amarillo); outline-offset: 3px; }
	a.boton svg { width: 20px; height: 20px; }

	footer {
		position: fixed;
		left: 0; right: 0; bottom: 0;
		padding: 1rem 1rem calc(1rem + env(safe-area-inset-bottom, 0px));
		border-top: 1px solid var(--linea);
		background: var(--negro);
		font-family: var(--f-mono);
		font-stretch: 85%;
		font-size: .7rem;
		letter-spacing: .08em;
		color: var(--gris);
	}

	@media (prefers-reduced-motion: reduce) {
		.wordmark .l, a.boton::before { transition: none; }
	}
</style>
</head>
<body>

	<div class="aviso">Estamos preparando café</div>

	<h1 class="wordmark" aria-label="First Coffee">
		<span class="palabra" aria-hidden="true"><span class="l">F</span><span class="l">I</span><span class="l">R</span><span class="l">S</span><span class="l">T</span></span>
		<span class="palabra" aria-hidden="true"><span class="l">C</span><span class="l">O</span><span class="l">F</span><span class="l">F</span><span class="l">E</span><span class="l">E</span></span>
	</h1>

	<p class="bajada">
		Nuestra web está casi lista. Muy pronto vas a poder conocer y cotizar
		el kit completo de mochila y termo para vender café en la calle.
	</p>

	<a class="boton" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
			<rect x="3" y="3" width="18" height="18" rx="5" />
			<circle cx="12" cy="12" r="4" />
			<circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
		</svg>
		Síguenos en Instagram
	</a>

	<footer>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> First Coffee Chile · Santiago</footer>

<script>
	// En pantallas táctiles no hay hover: al tocar o deslizar el dedo, la letra se enciende un momento.
	(function () {
		var letras = document.querySelectorAll('.wordmark .l');
		function encender(el) {
			if (!el || !el.classList || !el.classList.contains('l')) return;
			el.classList.add('on');
			clearTimeout(el._t);
			el._t = setTimeout(function () { el.classList.remove('on'); }, 600);
		}
		document.addEventListener('touchstart', function (e) { encender(e.target); }, { passive: true });
		document.addEventListener('touchmove', function (e) {
			var t = e.touches[0];
			encender(document.elementFromPoint(t.clientX, t.clientY));
		}, { passive: true });
	})();
</script>

</body>
</html>
	<?php
	exit;
}, 0 );
