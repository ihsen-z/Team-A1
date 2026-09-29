<?php
/**
 * Rendu du bloc factory/product-grid.
 *
 * Remplace une grille par page — produits mis en avant, page « packs »,
 * sélection d'univers — qui ne différaient que par la catégorie ciblée et une
 * limite, toutes deux codées en dur dans les thèmes d'origine.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! function_exists('wc_get_products')) {
	return '';
}

$categorie = isset($attributes['categorie']) ? sanitize_title((string) $attributes['categorie']) : '';
$limite    = isset($attributes['limite']) ? absint($attributes['limite']) : 12;
$limite    = max(1, min(48, $limite));
$tri       = isset($attributes['tri']) ? (string) $attributes['tri'] : 'menu_order';

$tris = array(
	'menu_order' => array( 'orderby' => 'menu_order', 'order' => 'ASC' ),
	'date'       => array( 'orderby' => 'date',       'order' => 'DESC' ),
	'price'      => array( 'orderby' => 'price',      'order' => 'ASC' ),
	'price-desc' => array( 'orderby' => 'price',      'order' => 'DESC' ),
	'popularity' => array( 'orderby' => 'popularity', 'order' => 'DESC' ),
	'rating'     => array( 'orderby' => 'rating',     'order' => 'DESC' ),
);

if (! isset($tris[ $tri ])) {
	$tri = 'menu_order';
}

$args = array(
	'status'  => 'publish',
	'limit'   => $limite,
	'orderby' => $tris[ $tri ]['orderby'],
	'order'   => $tris[ $tri ]['order'],
	'visibility' => 'catalog',
);

if ('' !== $categorie) {
	$args['category'] = array( $categorie );
}

$produits = wc_get_products($args);

if (! is_array($produits) || array() === $produits) {
	return '';
}

$titre     = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$colonnes  = isset($attributes['colonnes']) ? absint($attributes['colonnes']) : 4;
$colonnes  = max(1, min(6, $colonnes));
$compteur  = ! empty($attributes['compteur']);
$filtres   = ! empty($attributes['filtres']) && '' !== $categorie;
$variante  = isset($attributes['variante']) ? (string) $attributes['variante'] : 'complete';

if (! in_array($variante, array( 'complete', 'compacte' ), true)) {
	$variante = 'complete';
}

$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 2;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$sous_categories = array();

if ($filtres) {
	$parent = get_term_by('slug', $categorie, 'product_cat');

	if ($parent instanceof WP_Term) {
		$enfants = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $parent->term_id,
				'hide_empty' => true,
			)
		);

		if (! is_wp_error($enfants) && is_array($enfants)) {
			$sous_categories = $enfants;
		}
	}
}

$style    = '--factory-grid-cols:' . $colonnes . ';';
$titre_id = '' !== $titre ? wp_unique_id('factory-grid-titre-') : '';
$premier  = true;

$wrapper = get_block_wrapper_attributes(array( 'class' => 'factory-grid' ));
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	style="<?php echo esc_attr($style); ?>"
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<?php if ('' !== $titre || $compteur) : ?>
		<header class="factory-grid__entete">
			<?php if ('' !== $titre) : ?>
				<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-grid__titre">
					<?php echo esc_html($titre); ?>
				</<?php echo esc_html($tag); ?>>
			<?php endif; ?>

			<?php if ($compteur) : ?>
				<p class="factory-grid__compteur">
					<?php
					$total = count($produits);
					printf(
						/* translators: %s : nombre de produits. */
						esc_html(_n('%s produit', '%s produits', $total, 'factory-core')),
						esc_html(number_format_i18n($total))
					);
					?>
				</p>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<?php if (array() !== $sous_categories) : ?>
		<nav class="factory-grid__filtres" aria-label="<?php esc_attr_e('Filtrer par sous-catégorie', 'factory-core'); ?>">
			<ul class="factory-grid__pastilles" role="list">
				<?php foreach ($sous_categories as $sous) : ?>
					<?php
					$lien = get_term_link($sous);

					if (is_wp_error($lien)) {
						continue;
					}
					?>
					<li>
						<a class="factory-grid__pastille" href="<?php echo esc_url($lien); ?>">
							<?php echo esc_html($sous->name); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<ul class="factory-grid__liste" role="list">
		<?php foreach ($produits as $produit) : ?>
			<?php
			if (! $produit instanceof WC_Product) {
				continue;
			}
			?>
			<li class="factory-grid__item">
				<?php
				// La carte est rendue par son propre bloc : une seule carte à
				// maintenir, et la grille n'en connaît pas le balisage.
				echo render_block( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendu par le bloc factory/product-card, qui échappe ses sorties.
					array(
						'blockName'    => 'factory/product-card',
						'attrs'        => array(
							'produitId'   => $produit->get_id(),
							'variante'    => $variante,
							// Seule la première carte est prioritaire : le
							// navigateur ne peut pas précharger toute la grille.
							'prioritaire' => $premier,
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '',
						'innerContent' => array(),
					)
				);
				$premier = false;
				?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
