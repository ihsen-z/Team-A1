<?php
/**
 * Rendu du bloc factory/arguments-band.
 *
 * Généralise trois composants qui n'étaient distincts que par accident : la
 * barre de réassurance, la même rangée posée sur une photo, et la rangée de la
 * fiche produit.
 *
 * Le nombre de colonnes voyage en variable CSS plutôt qu'en classe : une
 * rangée de deux et une rangée de quatre partagent la même règle.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : array();

// Les arguments sont des promesses commerciales : aucune valeur par défaut,
// et pas de cadre vide si rien n'est renseigné.
$items = array_values(
	array_filter(
		$items,
		static function ($item): bool {
			return is_array($item) && isset($item['titre']) && '' !== (string) $item['titre'];
		}
	)
);

if (array() === $items) {
	return '';
}

$titre    = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$variante = isset($attributes['variante']) ? (string) $attributes['variante'] : 'barre';
$image_id = isset($attributes['imageId']) ? absint($attributes['imageId']) : 0;

if (! in_array($variante, array( 'barre', 'photo', 'cartes' ), true)) {
	$variante = 'barre';
}

// 0 signifie « autant de colonnes que d'items » : le cas le plus fréquent.
$colonnes = isset($attributes['colonnes']) ? absint($attributes['colonnes']) : 0;
$colonnes = $colonnes > 0 ? min(6, $colonnes) : count($items);

// Le niveau de titre est borné pour ne pas casser la hiérarchie de la page.
$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 2;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$style = '--factory-args-cols:' . $colonnes . ';';

foreach (array(
	'titre'      => 'couleurTitre',
	'texte'      => 'couleurTexte',
	'icone-fond' => 'couleurIconeFond',
	'icone'      => 'couleurIcone',
) as $nom => $attribut) {
	$hex = isset($attributes[ $attribut ]) ? sanitize_hex_color((string) $attributes[ $attribut ]) : null;
	if (is_string($hex) && '' !== $hex) {
		$style .= '--factory-args-' . $nom . ':' . $hex . ';';
	}
}

$a_image = 'photo' === $variante && $image_id > 0 && wp_attachment_is_image($image_id);

if ($a_image) {
	$url = wp_get_attachment_image_url($image_id, 'factory-hero');
	if (is_string($url)) {
		// Guillemets obligatoires : esc_url_raw() encode les guillemets mais pas
		// les parenthèses, qui fermeraient url() prématurément.
		$style .= '--factory-args-image:url("' . esc_url_raw($url) . '");';
	}
}

$titre_id = '' !== $titre ? wp_unique_id('factory-args-titre-') : '';

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'factory-args factory-args--' . sanitize_html_class($variante) )
);
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	style="<?php echo esc_attr($style); ?>"
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<?php if ('' !== $titre) : ?>
		<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-args__titre">
			<?php echo esc_html($titre); ?>
		</<?php echo esc_html($tag); ?>>
	<?php endif; ?>

	<ul class="factory-args__liste" role="list">
		<?php foreach ($items as $item) : ?>
			<?php
			$item_titre = (string) $item['titre'];
			$item_sous  = isset($item['sousTitre']) ? (string) $item['sousTitre'] : '';
			$item_url   = isset($item['url']) ? (string) $item['url'] : '';
			$item_icone = isset($item['iconeUrl']) ? (string) $item['iconeUrl'] : '';
			?>
			<li class="factory-args__item">
				<?php if ('' !== $item_icone) : ?>
					<span class="factory-args__icone">
						<?php /* Décorative : le titre de l'argument porte le sens. */ ?>
						<img src="<?php echo esc_url($item_icone); ?>" alt=""
							width="24" height="24" loading="lazy" decoding="async">
					</span>
				<?php endif; ?>

				<span class="factory-args__texte">
					<span class="factory-args__item-titre">
						<?php if ('' !== $item_url) : ?>
							<a href="<?php echo esc_url($item_url); ?>"><?php echo esc_html($item_titre); ?></a>
						<?php else : ?>
							<?php echo esc_html($item_titre); ?>
						<?php endif; ?>
					</span>

					<?php if ('' !== $item_sous) : ?>
						<span class="factory-args__item-sous"><?php echo esc_html($item_sous); ?></span>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
