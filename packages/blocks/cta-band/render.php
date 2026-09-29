<?php
/**
 * Rendu du bloc factory/cta-band.
 *
 * Un seul bouton, volontairement : un bandeau de conversion qui propose deux
 * actions n'en fait aboutir aucune.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$titre    = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$message  = isset($attributes['message']) ? (string) $attributes['message'] : '';
$cta_txt  = isset($attributes['ctaTexte']) ? (string) $attributes['ctaTexte'] : '';
$cta_url  = isset($attributes['ctaUrl']) ? (string) $attributes['ctaUrl'] : '';
$image_id = isset($attributes['imageId']) ? absint($attributes['imageId']) : 0;
$variante = isset($attributes['variante']) ? (string) $attributes['variante'] : 'primaire';
$degrade  = isset($attributes['degrade']) ? (string) $attributes['degrade'] : 'lateral';

// Sans message ni bouton, il n'y a pas d'appel à l'action.
if (('' === $message && '' === $titre) || '' === $cta_txt || '' === $cta_url) {
	return '';
}

if (! in_array($variante, array( 'primaire', 'accent', 'sobre', 'photo' ), true)) {
	$variante = 'primaire';
}

if (! in_array($degrade, array( 'lateral', 'vertical', 'aucun' ), true)) {
	$degrade = 'lateral';
}

$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 2;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$style = '';

foreach (array(
	'titre'        => 'couleurTitre',
	'texte'        => 'couleurTexte',
	'bouton'       => 'couleurBouton',
	'bouton-texte' => 'couleurBoutonTexte',
) as $nom => $attribut) {
	$hex = isset($attributes[ $attribut ]) ? sanitize_hex_color((string) $attributes[ $attribut ]) : null;
	if (is_string($hex) && '' !== $hex) {
		$style .= '--factory-cta-' . $nom . ':' . $hex . ';';
	}
}

$a_image = 'photo' === $variante && $image_id > 0 && wp_attachment_is_image($image_id);

if ($a_image) {
	$url = wp_get_attachment_image_url($image_id, 'factory-hero');
	if (is_string($url)) {
		// Guillemets : esc_url_raw() encode les guillemets mais pas les
		// parenthèses, qui fermeraient url() prématurément.
		$style .= '--factory-cta-image:url("' . esc_url_raw($url) . '");';
	}
}

$classes = 'factory-cta factory-cta--' . sanitize_html_class($variante)
	. ' factory-cta--degrade-' . sanitize_html_class($degrade);

$wrapper  = get_block_wrapper_attributes(array( 'class' => $classes ));
$titre_id = '' !== $titre ? wp_unique_id('factory-cta-titre-') : '';
?>
<aside <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	<?php if ('' !== $style) : ?>style="<?php echo esc_attr($style); ?>"<?php endif; ?>
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<div class="factory-cta__texte">
		<?php if ('' !== $titre) : ?>
			<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-cta__titre">
				<?php echo esc_html($titre); ?>
			</<?php echo esc_html($tag); ?>>
		<?php endif; ?>

		<?php if ('' !== $message) : ?>
			<p class="factory-cta__message"><?php echo esc_html($message); ?></p>
		<?php endif; ?>
	</div>

	<a class="wp-block-button__link factory-cta__bouton" href="<?php echo esc_url($cta_url); ?>">
		<?php echo esc_html($cta_txt); ?>
	</a>
</aside>
