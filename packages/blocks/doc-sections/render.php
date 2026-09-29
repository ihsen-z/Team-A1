<?php
/**
 * Rendu du bloc factory/doc-sections.
 *
 * Aucune donnée n'est dupliquée : par défaut le bloc lit le contenu de la page
 * courante et le découpe par niveau de titre. Une FAQ n'existe donc qu'à un
 * seul endroit, celui où l'administrateur l'écrit.
 *
 * @package FactoryCore
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne.
 * @var WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! function_exists('factory_core_split_by_heading')) {
	return '';
}

$source         = isset($attributes['source']) ? (string) $attributes['source'] : 'contenu';
$niveau_groupe  = isset($attributes['niveauGroupe']) ? (string) $attributes['niveauGroupe'] : 'h2';
$variante       = isset($attributes['variante']) ? (string) $attributes['variante'] : 'accordeon';
$sommaire       = ! empty($attributes['sommaire']);
$numerote       = ! empty($attributes['numerote']);
$ouvert         = ! empty($attributes['ouvertParDefaut']);

if (! in_array($variante, array( 'accordeon', 'cartes' ), true)) {
	$variante = 'accordeon';
}

$sections = array();

if ('manuelle' === $source) {
	$saisies = isset($attributes['sections']) && is_array($attributes['sections']) ? $attributes['sections'] : array();

	foreach ($saisies as $saisie) {
		if (! is_array($saisie) || empty($saisie['titre'])) {
			continue;
		}

		$sections[] = array(
			'title' => (string) $saisie['titre'],
			'html'  => isset($saisie['contenu']) ? wp_kses_post((string) $saisie['contenu']) : '',
		);
	}
} else {
	$post = get_post();

	if (! $post instanceof WP_Post) {
		return '';
	}

	/*
	 * Ce bloc lit le contenu de la page où il se trouve : appliquer
	 * « the_content » relancerait le rendu des blocs, donc celui-ci, sans fin.
	 * Le garde-fou coupe la récursion ; le contenu est ensuite rendu avec
	 * do_blocks() + wpautop(), sans repasser par le filtre complet.
	 */
	static $en_cours = false;

	if ($en_cours) {
		return '';
	}

	$en_cours = true;

	// Le bloc se retire du contenu qu'il va découper : sans cela, sa propre
	// balise se retrouverait dans la première section.
	$brut = preg_replace(
		'#<!--\s*wp:factory/doc-sections\b.*?(/-->|<!--\s*/wp:factory/doc-sections\s*-->)#s',
		'',
		$post->post_content
	);

	$html = do_blocks(is_string($brut) ? $brut : $post->post_content);
	$html = wptexturize($html);
	$html = wpautop($html);
	$html = do_shortcode($html);

	$decoupe  = factory_core_split_by_heading($html, $niveau_groupe);
	$sections = $decoupe['sections'];

	$en_cours = false;
}

if (array() === $sections) {
	return '';
}

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'factory-doc factory-doc--' . sanitize_html_class($variante) )
);

$base_id = wp_unique_id('factory-doc-');
$rang    = 0;
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>>

	<?php if ($sommaire) : ?>
		<nav class="factory-doc__sommaire" aria-label="<?php esc_attr_e('Sommaire', 'factory-core'); ?>">
			<ol class="factory-doc__sommaire-liste">
				<?php foreach ($sections as $index => $section) : ?>
					<li>
						<a href="#<?php echo esc_attr($base_id . '-' . $index); ?>">
							<?php echo esc_html(factory_core_strip_leading_number($section['title'])); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<div class="factory-doc__sections">
		<?php foreach ($sections as $index => $section) : ?>
			<?php
			++$rang;
			$titre  = factory_core_strip_leading_number($section['title']);
			$item_id = $base_id . '-' . $index;
			?>
			<?php if ('accordeon' === $variante) : ?>
				<?php /* <details> : l'ouverture et la fermeture fonctionnent sans JavaScript. */ ?>
				<details class="factory-doc__item" id="<?php echo esc_attr($item_id); ?>" <?php echo $ouvert ? 'open' : ''; ?>>
					<summary class="factory-doc__titre">
						<?php if ($numerote) : ?>
							<span class="factory-doc__numero"><?php echo esc_html((string) $rang); ?>.</span>
						<?php endif; ?>
						<span><?php echo esc_html($titre); ?></span>
					</summary>
					<div class="factory-doc__contenu">
						<?php echo wp_kses_post($section['html']); ?>
					</div>
				</details>
			<?php else : ?>
				<article class="factory-doc__item" id="<?php echo esc_attr($item_id); ?>">
					<h3 class="factory-doc__titre">
						<?php if ($numerote) : ?>
							<span class="factory-doc__numero"><?php echo esc_html((string) $rang); ?>.</span>
						<?php endif; ?>
						<?php echo esc_html($titre); ?>
					</h3>
					<div class="factory-doc__contenu">
						<?php echo wp_kses_post($section['html']); ?>
					</div>
				</article>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
