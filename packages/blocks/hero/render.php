<?php
/**
 * Rendu du bloc factory/hero.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$titre      = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$emphase    = isset($attributes['titreEmphase']) ? (string) $attributes['titreEmphase'] : '';
$accroche   = isset($attributes['accroche']) ? (string) $attributes['accroche'] : '';
$cta_txt    = isset($attributes['ctaTexte']) ? (string) $attributes['ctaTexte'] : '';
$cta_url    = isset($attributes['ctaUrl']) ? (string) $attributes['ctaUrl'] : '';
$cta2_txt   = isset($attributes['ctaSecTexte']) ? (string) $attributes['ctaSecTexte'] : '';
$cta2_url   = isset($attributes['ctaSecUrl']) ? (string) $attributes['ctaSecUrl'] : '';
$image_id   = isset($attributes['imageId']) ? absint($attributes['imageId']) : 0;
$mobile_id  = isset($attributes['imageMobileId']) ? absint($attributes['imageMobileId']) : 0;
$hauteur    = isset($attributes['hauteur']) ? (string) $attributes['hauteur'] : 'moyenne';
$prioritaire = ! empty($attributes['prioritaire']);

if ('' === $titre) {
	return '';
}

if (! in_array($hauteur, array( 'compacte', 'moyenne', 'pleine' ), true)) {
	$hauteur = 'moyenne';
}

// Le niveau est borné : un hero ne doit jamais casser la hiérarchie des titres.
$niveau = isset($attributes['niveau']) ? absint($attributes['niveau']) : 1;
$niveau = max(1, min(3, $niveau));
$tag    = 'h' . $niveau;

// Le voile garantit la lisibilité du texte sur n'importe quelle photo.
$opacite = isset($attributes['opaciteVoile']) ? absint($attributes['opaciteVoile']) : 45;
$opacite = min(90, $opacite);

$style   = '--factory-hero-voile:' . ($opacite / 100) . ';';
$a_image = $image_id > 0 && wp_attachment_is_image($image_id);

$classes = 'factory-hero factory-hero--' . sanitize_html_class($hauteur);
if ($a_image) {
	$classes .= ' factory-hero--image';
}

$wrapper  = get_block_wrapper_attributes(array( 'class' => $classes ));
$titre_id = wp_unique_id('factory-hero-titre-');
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	style="<?php echo esc_attr($style); ?>" aria-labelledby="<?php echo esc_attr($titre_id); ?>">

	<?php if ($a_image) : ?>
		<?php
		/*
		 * <picture> plutôt qu'un fond CSS : l'image du hero est le plus gros
		 * élément de la page, et seul un vrai <img> accepte fetchpriority.
		 * « prioritaire » ne doit être activé que sur un seul bloc par page.
		 */
		$attrs = array(
			'class'    => 'factory-hero__img',
			'decoding' => 'async',
		);

		if ($prioritaire) {
			$attrs['loading']       = 'eager';
			$attrs['fetchpriority'] = 'high';
		} else {
			$attrs['loading'] = 'lazy';
		}
		?>
		<picture class="factory-hero__media">
			<?php if ($mobile_id > 0 && wp_attachment_is_image($mobile_id)) : ?>
				<?php $mobile_src = wp_get_attachment_image_url($mobile_id, 'large'); ?>
				<?php if (is_string($mobile_src)) : ?>
					<source media="(max-width: 47.99em)" srcset="<?php echo esc_url($mobile_src); ?>">
				<?php endif; ?>
			<?php endif; ?>
			<?php
			echo wp_get_attachment_image($image_id, 'factory-hero', false, $attrs); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() échappe ses attributs.
			?>
		</picture>
	<?php endif; ?>

	<div class="factory-hero__inner">
		<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-hero__titre">
			<?php echo esc_html($titre); ?>
			<?php if ('' !== $emphase) : ?>
				<em class="factory-hero__emphase"><?php echo esc_html($emphase); ?></em>
			<?php endif; ?>
		</<?php echo esc_html($tag); ?>>

		<?php if ('' !== $accroche) : ?>
			<p class="factory-hero__accroche"><?php echo esc_html($accroche); ?></p>
		<?php endif; ?>

		<?php if (('' !== $cta_txt && '' !== $cta_url) || ('' !== $cta2_txt && '' !== $cta2_url)) : ?>
			<p class="factory-hero__actions">
				<?php if ('' !== $cta_txt && '' !== $cta_url) : ?>
					<a class="wp-block-button__link factory-hero__cta" href="<?php echo esc_url($cta_url); ?>">
						<?php echo esc_html($cta_txt); ?>
					</a>
				<?php endif; ?>

				<?php if ('' !== $cta2_txt && '' !== $cta2_url) : ?>
					<a class="factory-hero__cta factory-hero__cta--secondaire" href="<?php echo esc_url($cta2_url); ?>">
						<?php echo esc_html($cta2_txt); ?>
					</a>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
</section>
