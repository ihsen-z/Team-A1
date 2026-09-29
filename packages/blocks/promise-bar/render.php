<?php
/**
 * Rendu du bloc factory/promise-bar.
 *
 * Les promesses affichées ici sont des engagements commerciaux : elles ne
 * doivent jamais être inventées ni conservées en valeur par défaut. Une liste
 * vide ne rend rien.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : array();

if (array() === $items) {
	return '';
}

$accentuer = ! empty($attributes['accentuerPremier']);
$defilement = ! empty($attributes['defilement']);

// Au-delà de ce rang, les promesses sont masquées en mobile : une barre fine
// qui passe à la ligne trois fois cesse d'être une barre.
$seuil_mobile = isset($attributes['masquerMobileAuDela']) ? absint($attributes['masquerMobileAuDela']) : 1;
$seuil_mobile = max(1, $seuil_mobile);

$classes = 'factory-promises';
if ($defilement) {
	$classes .= ' factory-promises--defilement';
}

$wrapper = get_block_wrapper_attributes(array( 'class' => $classes ));
$rang    = 0;
?>
<aside <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>>
	<ul class="factory-promises__liste" role="list">
		<?php foreach ($items as $item) : ?>
			<?php
			if (! is_array($item)) {
				continue;
			}

			$libelle = isset($item['libelle']) ? (string) $item['libelle'] : '';

			if ('' === $libelle) {
				continue;
			}

			++$rang;

			$classe = 'factory-promises__item';
			if ($accentuer && 1 === $rang) {
				$classe .= ' is-accent';
			}
			if ($rang > $seuil_mobile) {
				$classe .= ' factory-promises__item--secondaire';
			}

			$icone_url = isset($item['iconeUrl']) ? (string) $item['iconeUrl'] : '';
			?>
			<li class="<?php echo esc_attr($classe); ?>">
				<?php if ('' !== $icone_url) : ?>
					<?php /* L'icône est décorative : le libellé porte déjà le sens. */ ?>
					<img class="factory-promises__icone" src="<?php echo esc_url($icone_url); ?>"
						alt="" width="20" height="20" loading="lazy" decoding="async">
				<?php endif; ?>
				<span class="factory-promises__libelle"><?php echo esc_html($libelle); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</aside>
