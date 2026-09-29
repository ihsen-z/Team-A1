<?php
/**
 * Rendu du bloc factory/reviews-wall.
 *
 * Un avis est une parole de client : jamais de valeur par défaut, jamais de
 * texte d'exemple. Sans avis, le bloc ne rend rien.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$source      = isset($attributes['source']) ? (string) $attributes['source'] : 'manuelle';
$nombre      = isset($attributes['nombre']) ? absint($attributes['nombre']) : 3;
$nombre      = max(1, min(12, $nombre));
$note_min    = isset($attributes['noteMinimale']) ? absint($attributes['noteMinimale']) : 4;
$note_min    = max(1, min(5, $note_min));
$longueur    = isset($attributes['longueurMax']) ? absint($attributes['longueurMax']) : 34;
$longueur    = max(10, min(120, $longueur));
$anonymiser  = ! empty($attributes['anonymiser']);

$avis = array();

if ('woocommerce' === $source && class_exists('WooCommerce')) {
	$commentaires = get_comments(
		array(
			'type'       => 'review',
			'status'     => 'approve',
			'number'     => $nombre,
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- volume borné par « number ».
				array(
					'key'     => 'rating',
					'value'   => $note_min,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	foreach ($commentaires as $commentaire) {
		if (! $commentaire instanceof WP_Comment) {
			continue;
		}

		$avis[] = array(
			'note'   => (int) get_comment_meta($commentaire->comment_ID, 'rating', true),
			'texte'  => wp_trim_words($commentaire->comment_content, $longueur),
			'auteur' => $commentaire->comment_author,
		);
	}
} else {
	$saisis = isset($attributes['avis']) && is_array($attributes['avis']) ? $attributes['avis'] : array();

	foreach ($saisis as $saisi) {
		if (! is_array($saisi) || empty($saisi['texte'])) {
			continue;
		}

		$avis[] = array(
			'note'   => isset($saisi['note']) ? (int) $saisi['note'] : 5,
			'texte'  => wp_trim_words((string) $saisi['texte'], $longueur),
			'auteur' => isset($saisi['auteur']) ? (string) $saisi['auteur'] : '',
		);
	}

	$avis = array_slice($avis, 0, $nombre);
}

if (array() === $avis) {
	return '';
}

$titre    = isset($attributes['titre']) ? (string) $attributes['titre'] : '';
$colonnes = isset($attributes['colonnes']) ? absint($attributes['colonnes']) : 3;
$colonnes = max(1, min(4, $colonnes));

$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 2;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$style    = '--factory-reviews-cols:' . $colonnes . ';';
$titre_id = '' !== $titre ? wp_unique_id('factory-reviews-titre-') : '';

$wrapper = get_block_wrapper_attributes(array( 'class' => 'factory-reviews' ));
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>
	style="<?php echo esc_attr($style); ?>"
	<?php if ('' !== $titre_id) : ?>aria-labelledby="<?php echo esc_attr($titre_id); ?>"<?php endif; ?>>

	<?php if ('' !== $titre) : ?>
		<<?php echo esc_html($tag); ?> id="<?php echo esc_attr($titre_id); ?>" class="factory-reviews__titre">
			<?php echo esc_html($titre); ?>
		</<?php echo esc_html($tag); ?>>
	<?php endif; ?>

	<ul class="factory-reviews__liste" role="list">
		<?php foreach ($avis as $item) : ?>
			<?php
			$note = max(0, min(5, (int) $item['note']));

			$auteur = trim((string) $item['auteur']);

			// « Sami Ben Salah » devient « Sami B. » : on cite un client, pas
			// un annuaire. L'initiale suffit à rendre l'avis crédible.
			if ($anonymiser && '' !== $auteur) {
				$morceaux = preg_split('/\s+/', $auteur) ?: array();
				if (count($morceaux) > 1) {
					$dernier = (string) end($morceaux);
					$auteur  = $morceaux[0] . ' ' . mb_strtoupper(mb_substr($dernier, 0, 1)) . '.';
				}
			}
			?>
			<li class="factory-reviews__item">
				<?php if ($note > 0) : ?>
					<?php /* Le pourcentage porte le remplissage : aucune image, aucune police d'icônes. */ ?>
					<span class="factory-reviews__note" style="--factory-rating:<?php echo esc_attr((string) ($note * 20)); ?>%"
						role="img"
						aria-label="<?php
						/* translators: %d : note sur cinq. */
						echo esc_attr(sprintf(_n('%d étoile sur 5', '%d étoiles sur 5', $note, 'factory-core'), $note));
						?>"></span>
				<?php endif; ?>

				<blockquote class="factory-reviews__texte">
					<p><?php echo esc_html($item['texte']); ?></p>
				</blockquote>

				<?php if ('' !== $auteur) : ?>
					<p class="factory-reviews__auteur"><?php echo esc_html($auteur); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
