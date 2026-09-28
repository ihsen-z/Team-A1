<?php
/**
 * Rendu du bloc factory/services-grid.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$titre    = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$items    = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : array();
$colonnes = isset($attributes['colonnes']) ? absint($attributes['colonnes']) : 3;
$colonnes = max(1, min(4, $colonnes));

if (array() === $items) {
	return '';
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'factory-services factory-services--cols-' . $colonnes,
	)
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>>
	<?php if ('' !== $titre) : ?>
		<h2 class="factory-services__titre"><?php echo esc_html($titre); ?></h2>
	<?php endif; ?>

	<ul class="factory-services__liste" role="list">
		<?php foreach ($items as $item) : ?>
			<?php
			if (! is_array($item)) {
				continue;
			}

			$item_titre = isset($item['titre']) ? (string) $item['titre'] : '';
			$item_texte = isset($item['texte']) ? (string) $item['texte'] : '';
			$item_url   = isset($item['url']) ? (string) $item['url'] : '';

			if ('' === $item_titre) {
				continue;
			}
			?>
			<li class="factory-services__item">
				<h3 class="factory-services__item-titre">
					<?php if ('' !== $item_url) : ?>
						<a href="<?php echo esc_url($item_url); ?>"><?php echo esc_html($item_titre); ?></a>
					<?php else : ?>
						<?php echo esc_html($item_titre); ?>
					<?php endif; ?>
				</h3>

				<?php if ('' !== $item_texte) : ?>
					<p class="factory-services__item-texte"><?php echo esc_html($item_texte); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
