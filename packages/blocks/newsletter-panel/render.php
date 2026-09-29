<?php
/**
 * Rendu du bloc factory/newsletter-panel.
 *
 * Le formulaire poste directement chez le fournisseur d'e-mailing : le site
 * ne reçoit ni ne stocke aucune adresse. C'est ce qui permet de se passer de
 * traitement côté serveur, donc de nonce — il n'y a pas d'action mutative ici.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$titre       = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$texte       = isset($attributes['texte']) ? (string) $attributes['texte'] : '';
$bouton      = isset($attributes['boutonTexte']) ? (string) $attributes['boutonTexte'] : '';
$placeholder = isset($attributes['champPlaceholder']) ? (string) $attributes['champPlaceholder'] : '';
$champ_nom   = isset($attributes['champNom']) ? sanitize_key((string) $attributes['champNom']) : 'EMAIL';
$image_id    = isset($attributes['imageId']) ? absint($attributes['imageId']) : 0;
$position    = isset($attributes['positionImage']) ? (string) $attributes['positionImage'] : 'gauche';

// Sans titre ni bouton, le panneau ne porte aucun message : ne rien rendre
// vaut mieux qu'un cadre vide en production.
if ('' === $titre && '' === $bouton) {
	return '';
}

if (! in_array($position, array( 'gauche', 'droite' ), true)) {
	$position = 'gauche';
}

if ('' === $champ_nom) {
	$champ_nom = 'EMAIL';
}

$action = isset($attributes['formulaireUrl']) ? (string) $attributes['formulaireUrl'] : '';
$action = '' !== $action ? wp_http_validate_url($action) : false;

// Les couleurs choisies deviennent des variables locales ; la feuille de style
// les lit avec un repli sur les jetons du thème, donc un champ vide n'est pas
// un trou mais la valeur par défaut.
$style   = '';
$couleurs = array(
	'panneau'      => 'couleurPanneau',
	'titre'        => 'couleurTitre',
	'texte'        => 'couleurTexte',
	'bouton'       => 'couleurBouton',
	'bouton-texte' => 'couleurBoutonTexte',
);

foreach ($couleurs as $nom => $attribut) {
	$hex = isset($attributes[ $attribut ]) ? sanitize_hex_color((string) $attributes[ $attribut ]) : null;
	if (is_string($hex) && '' !== $hex) {
		$style .= '--factory-news-' . $nom . ':' . $hex . ';';
	}
}

$titre_id = '' !== $titre ? wp_unique_id('factory-news-titre-') : '';
$champ_id = wp_unique_id('factory-news-champ-');

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'factory-news factory-news--image-' . sanitize_html_class($position) )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	<?php if ('' !== $style) : ?>style="<?php echo esc_attr($style); ?>"<?php endif; ?>
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<?php if ($image_id > 0 && wp_attachment_is_image($image_id)) : ?>
		<div class="factory-news__media">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'large',
				false,
				array(
					'class'    => 'factory-news__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() échappe ses attributs.
			?>
		</div>
	<?php endif; ?>

	<div class="factory-news__panel">
		<?php if ('' !== $titre) : ?>
			<h2 id="<?php echo esc_attr($titre_id); ?>" class="factory-news__titre">
				<?php echo esc_html($titre); ?>
			</h2>
		<?php endif; ?>

		<?php if ('' !== $texte) : ?>
			<p class="factory-news__texte"><?php echo esc_html($texte); ?></p>
		<?php endif; ?>

		<?php if ('' !== $bouton && is_string($action)) : ?>
			<form class="factory-news__form" action="<?php echo esc_url($action); ?>" method="post" target="_blank" rel="noopener">
				<label class="screen-reader-text" for="<?php echo esc_attr($champ_id); ?>">
					<?php echo esc_html($placeholder !== '' ? $placeholder : __('Votre adresse e-mail', 'factory-core')); ?>
				</label>
				<input
					class="factory-news__champ"
					id="<?php echo esc_attr($champ_id); ?>"
					type="email"
					name="<?php echo esc_attr($champ_nom); ?>"
					placeholder="<?php echo esc_attr($placeholder); ?>"
					autocomplete="email"
					required>
				<button class="factory-news__bouton" type="submit"><?php echo esc_html($bouton); ?></button>
			</form>
		<?php elseif ('' !== $bouton && current_user_can('manage_options')) : ?>
			<?php /* Signalé au seul administrateur : un visiteur ne doit pas voir la configuration manquante. */ ?>
			<p class="factory-news__avertissement" role="status">
				<?php esc_html_e('Panneau newsletter : l’URL du formulaire est absente ou invalide. Renseignez-la pour afficher le champ d’inscription.', 'factory-core'); ?>
			</p>
		<?php endif; ?>
	</div>
</section>
