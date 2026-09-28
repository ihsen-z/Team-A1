<?php
/**
 * Bloc « Rejoignez notre communauté » — avant le pied de page de l'accueil.
 *
 * Maquette « EVASIONS Newsletter » : photo à gauche (en haut sur mobile),
 * panneau vert à droite avec titre, sous-titre et inscription.
 *
 * Le formulaire poste chez le fournisseur d'e-mailing du site
 * (`evasions_newsletter_form()`) : le thème ne stocke aucune adresse.
 *
 * Contenu, couleurs, image et lien viennent du Customizer
 * (`evasions_community()`) : rien n'est écrit en dur (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$com  = evasions_community();
$news = evasions_newsletter_form();

// Sans titre ni bouton, il n'y a pas de message à porter : on ne rend rien.
if ( ! $com['enabled'] || ( '' === $com['title'] && '' === $com['button'] ) ) {
	return;
}

// Les couleurs choisies deviennent des variables locales, que la feuille de
// style lit avec un repli sur les jetons du thème.
$style = '';
foreach ( $com['colors'] as $name => $hex ) {
	$style .= '--ev-com-' . $name . ':' . $hex . ';';
}
?>
<section class="ev-com-wrap" <?php echo '' !== $style ? 'style="' . esc_attr( $style ) . '"' : ''; ?> <?php echo '' !== $com['title'] ? 'aria-labelledby="ev-com-title"' : ''; ?>>
	<div class="ev-container">
		<div class="ev-com">
			<div class="ev-com__media">
				<?php
				echo evasions_section_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image aux URL/attributs échappés.
					'community',
					'univers-plage.webp',
					array(
						'img_class' => 'ev-com__img',
						'width'     => 612,
						'height'    => 408,
						'loading'   => 'lazy',
						'decoding'  => 'async',
						'size'      => 'large',
					)
				);
				?>
			</div>
			<div class="ev-com__panel">
				<?php if ( '' !== $com['title'] ) : ?>
					<h2 id="ev-com-title" class="ev-com__title"><?php echo esc_html( $com['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $com['text'] ) : ?>
					<p class="ev-com__text"><?php echo esc_html( $com['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $com['button'] ) : ?>
					<?php // Le fournisseur d'e-mailing reçoit l'adresse et gère la confirmation : nouvelle fenêtre pour ne pas faire perdre sa page à l'acheteur. Sans adresse renseignée, pas d'attribut action ni de nouvelle fenêtre. ?>
					<form class="ev-com__form" <?php echo '' !== $news['action'] ? 'action="' . esc_url( $news['action'] ) . '" target="_blank" rel="noopener"' : ''; ?> method="post">
						<label class="screen-reader-text" for="ev-com-email"><?php esc_html_e( 'Adresse e-mail', 'evasions' ); ?></label>
						<input
							class="ev-com__field"
							id="ev-com-email"
							type="email"
							name="<?php echo esc_attr( $news['field'] ); ?>"
							required
							autocomplete="email"
							<?php echo '' !== $news['placeholder'] ? 'placeholder="' . esc_attr( $news['placeholder'] ) . '"' : ''; ?>
						>
						<button class="ev-com__btn" type="submit"><?php echo esc_html( $com['button'] ); ?></button>
					</form>
					<?php
					// Garde-fou pour l'administrateur : un champ qui n'envoie rien se
					// voit tout de suite, sans rien afficher aux acheteurs (§60).
					if ( '' === $news['action'] && current_user_can( 'manage_options' ) ) :
						?>
						<p class="ev-com__warning"><?php esc_html_e( 'Administrateur : ce formulaire n’envoie encore nulle part. Renseignez « Communauté — adresse du formulaire » dans Personnaliser.', 'evasions' ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
