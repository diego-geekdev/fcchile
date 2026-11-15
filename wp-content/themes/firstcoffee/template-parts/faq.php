<?php
/**
 * Preguntas frecuentes (First Coffee → Preguntas).
 */
$fc_faq = fct_items( 'fc_faq' );
if ( ! $fc_faq ) {
	return;
}
$fc_wa = fct_whatsapp( 'Hola, tengo una pregunta sobre el kit' );
?>
<section class="sec wrap" id="faq" style="padding-top:0">
	<div class="sec-head rv"><span class="eyebrow">Preguntas</span><h2>Preguntas frecuentes</h2></div>
	<div class="faq rv">
		<?php foreach ( $fc_faq as $fc_f ) : ?>
			<details><summary><?php echo esc_html( get_the_title( $fc_f ) ); ?><span class="pm" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 14 14" stroke="var(--cream)" stroke-width="2" stroke-linecap="round"><path d="M7 1v12M1 7h12"/></svg></span></summary><div class="ans"><?php echo wp_kses_post( wpautop( $fc_f->post_content ) ); ?></div></details>
		<?php endforeach; ?>
		<div class="faq-more"><span>¿No está tu pregunta?</span><a class="btn ghost small" href="<?php echo esc_url( $fc_wa ); ?>" <?php echo '#cotizar' === $fc_wa ? '' : 'target="_blank" rel="noopener"'; ?>>Escríbenos por WhatsApp</a></div>
	</div>
</section>
