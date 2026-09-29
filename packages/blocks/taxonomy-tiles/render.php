<?php
/**
 * Rendu du bloc factory/taxonomy-tiles.
 *
 * Le titre, la description et l'image de chaque tuile viennent du terme de
 * taxonomie. C'est le point de la refonte : un tableau PHP codé en dur rend un
 * thème non réutilisable, et se retrouve consommé par cinq endroits différents
 * dès qu'on le laisse s'installer.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$taxonomie = isset($attributes['taxonomie']) ? sanitize_key((string) $attributes['taxonomie']) : 'category';

if ('' === $taxonomie || ! taxonomy_exists($taxonomie)) {
	return '';
}

$source  = isset($attributes['source']) ? (string) $attributes['source'] : 'manuelle';
$limite  = isset($attributes['limite']) ? absint($attributes['limite']) : 6;
$limite  = max(1, min(24, $limite));

$args = array(
	'taxonomy'   => $taxonomie,
	'hide_empty' => true,
	'number'     => $limite,
);

if ('manuelle' === $source) {
	$termes_ids = isset($attributes['termes']) && is_array($attributes['termes'])
		? array_values(array_filter(array_map('absint', $attributes['termes'])))
		: array();

	if (array() === $termes_ids) {
		return '';
	}

	$args['include'] = $termes_ids;
	$args['orderby'] = 'include';
	$args['number']  = 0;
} else {
	// En mode automatique, seuls les termes de premier niveau : une tuile par
	// rayon, pas une tuile par sous-rayon.
	$args['parent']  = 0;
	$args['orderby'] = 'name';
}

$termes = get_terms($args);

if (is_wp_error($termes) || ! is_array($termes) || array() === $termes) {
	return '';
}

$titre     = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$cta       = isset($attributes['libelleCta']) ? (string) $attributes['libelleCta'] : '';
$sous      = ! empty($attributes['afficheSousTitre']);
$ratio     = isset($attributes['ratio']) ? (string) $attributes['ratio'] : '3:2';
$colonnes  = isset($attributes['colonnes']) ? absint($attributes['colonnes']) : 2;
$colonnes  = max(1, min(4, $colonnes));

if (! in_array($ratio, array( '1:1', '3:2', '4:3', '16:9' ), true)) {
	$ratio = '3:2';
}

$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 2;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$style    = '--factory-tiles-cols:' . $colonnes . ';--factory-tiles-ratio:' . str_replace(':', '/', $ratio) . ';';
$titre_id = '' !== $titre ? wp_unique_id('factory-tiles-titre-') : '';

$wrapper = get_block_wrapper_attributes(array( 'class' => 'factory-tiles' ));
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	style="<?php echo esc_attr($style); ?>"
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<?php if ('' !== $titre) : ?>
		<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-tiles__titre">
			<?php echo esc_html($titre); ?>
		</<?php echo esc_html($tag); ?>>
	<?php endif; ?>

	<ul class="factory-tiles__liste" role="list">
		<?php foreach ($termes as $terme) : ?>
			<?php
			if (! $terme instanceof WP_Term) {
				continue;
			}

			$lien = get_term_link($terme);

			if (is_wp_error($lien)) {
				continue;
			}

			// L'image du terme est posée par WooCommerce (thumbnail_id) ou par
			// un champ personnalisé ; absente, la tuile reste lisible.
			$image_id = (int) get_term_meta($terme->term_id, 'thumbnail_id', true);
			$description = $sous ? trim(wp_strip_all_tags($terme->description)) : '';
			?>
			<li class="factory-tiles__item">
				<a class="factory-tiles__lien" href="<?php echo esc_url($lien); ?>">
					<?php if ($image_id > 0 && wp_attachment_is_image($image_id)) : ?>
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'factory-card',
							false,
							array(
								'class'    => 'factory-tiles__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() échappe ses attributs.
						?>
					<?php else : ?>
						<span class="factory-tiles__img factory-tiles__img--vide" aria-hidden="true"></span>
					<?php endif; ?>

					<span class="factory-tiles__contenu">
						<span class="factory-tiles__nom"><?php echo esc_html($terme->name); ?></span>

						<?php if ('' !== $description) : ?>
							<span class="factory-tiles__desc"><?php echo esc_html($description); ?></span>
						<?php endif; ?>

						<?php if ('' !== $cta) : ?>
							<?php /* Repère visuel, pas un second lien : la tuile entière est cliquable. */ ?>
							<span class="factory-tiles__cta" aria-hidden="true"><?php echo esc_html($cta); ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
