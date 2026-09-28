<?php
/**
 * Rendu du bloc factory/hero.
 *
 * @package FactoryCore
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne.
 * @var WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$titre    = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$accroche = isset($attributes['accroche']) ? (string) $attributes['accroche'] : '';
$cta_txt  = isset($attributes['ctaTexte']) ? (string) $attributes['ctaTexte'] : '';
$cta_url  = isset($attributes['ctaUrl']) ? (string) $attributes['ctaUrl'] : '';
$image_id = isset($attributes['imageId']) ? absint($attributes['imageId']) : 0;
$hauteur  = isset($attributes['hauteur']) ? (string) $attributes['hauteur'] : 'moyenne';

// Le niveau de titre est borné : un hero ne doit jamais casser la hiérarchie
// des titres de la page, c'est une régression d'accessibilité classique.
$niveau = isset($attributes['niveau']) ? absint($attributes['niveau']) : 1;
$niveau = max(1, min(3, $niveau));
$tag    = 'h' . $niveau;

if ('' === $titre) {
	return '';
}

$classes = 'factory-hero factory-hero--' . sanitize_html_class($hauteur);
$wrapper = get_block_wrapper_attributes(array( 'class' => $classes ));

$style = '';
if ($image_id > 0) {
	$url = wp_get_attachment_image_url($image_id, 'factory-hero');
	if (is_string($url)) {
		$style = sprintf('background-image:url(%s)', esc_url($url));
	}
}
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	<?php if ('' !== $style) : ?>style="<?php echo esc_attr($style); ?>"<?php endif; ?>>
	<div class="factory-hero__inner">
		<<?php echo esc_html($tag); ?> class="factory-hero__titre">
			<?php echo esc_html($titre); ?>
		</<?php echo esc_html($tag); ?>>

		<?php if ('' !== $accroche) : ?>
			<p class="factory-hero__accroche"><?php echo esc_html($accroche); ?></p>
		<?php endif; ?>

		<?php if ('' !== $cta_txt && '' !== $cta_url) : ?>
			<p class="factory-hero__actions">
				<a class="wp-block-button__link factory-hero__cta" href="<?php echo esc_url($cta_url); ?>">
					<?php echo esc_html($cta_txt); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</section>
