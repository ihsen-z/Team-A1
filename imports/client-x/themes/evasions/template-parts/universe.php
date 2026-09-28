<?php
/**
 * Page univers : hero, sous-catégories, bloc éditorial, sélection.
 *
 * Tout vient des catégories WooCommerce : le titre est le nom de la catégorie,
 * la phrase sous le titre est sa description (à défaut, celle de la maquette
 * pour l'univers qu'elle dessine), les pastilles sont ses sous-catégories, la
 * sélection ses produits mis en avant. Voir `inc/universe.php`.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$term = evasions_universe_term();

if ( ! $term instanceof WP_Term ) {
	return;
}

$hero      = evasions_universe_hero( $term );
$children  = evasions_universe_children( $term );
$editorial = evasions_universe_editorial( $term );
$products  = evasions_featured_products( 4, $term->slug );
$all_url   = evasions_universe_all_url( $term );
?>
<section class="ev-uhero<?php echo '' === $hero['image'] ? ' ev-uhero--plain' : ''; ?>" aria-labelledby="ev-uhero-title">
	<?php if ( $hero['id'] > 0 ) : ?>
		<?php
		// Image de la catégorie : WordPress fournit le srcset, le mobile ne télécharge pas le fichier de 2400 px.
		echo wp_get_attachment_image(
			$hero['id'],
			'full',
			false,
			array(
				'class'         => 'ev-uhero__img',
				'alt'           => '',
				'sizes'         => '100vw',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
			)
		); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup d'image WordPress.
		?>
	<?php elseif ( '' !== $hero['image'] ) : ?>
		<img class="ev-uhero__img" src="<?php echo esc_url( $hero['image'] ); ?>" width="612" height="408" alt="" decoding="async" fetchpriority="high">
	<?php endif; ?>
	<div class="ev-uhero__shade" aria-hidden="true"></div>
	<div class="ev-container ev-uhero__copy">
		<h1 id="ev-uhero-title"><?php echo esc_html( $term->name ); ?></h1>
		<?php if ( '' !== $hero['tagline'] ) : ?>
			<p><?php echo esc_html( $hero['tagline'] ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( array() !== $children ) : ?>
	<nav class="ev-usubs" aria-label="<?php esc_attr_e( 'Sous-catégories', 'evasions' ); ?>">
		<ul class="ev-container ev-usubs__list">
			<?php foreach ( $children as $child ) : ?>
				<li>
					<a href="<?php echo esc_url( $child['url'] ); ?>">
						<span class="ev-usubs__thumb">
							<?php if ( '' !== $child['image'] ) : ?>
								<img src="<?php echo esc_url( $child['image'] ); ?>" alt="" loading="lazy" decoding="async">
							<?php else : ?>
								<span aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $child['name'], 0, 1 ) ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="ev-usubs__name"><?php echo esc_html( $child['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
<?php endif; ?>

<?php if ( null !== $editorial ) : ?>
	<section class="ev-ued" aria-labelledby="ev-ued-title">
		<div class="ev-container">
			<div class="ev-ued__card">
				<?php echo evasions_picture( $editorial['image'], array( 'img_class' => 'ev-ued__img', 'width' => 612, 'height' => 408, 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup <picture> échappé dans evasions_picture(). ?>
				<div class="ev-ued__shade" aria-hidden="true"></div>
				<div class="ev-ued__copy">
					<h2 id="ev-ued-title"><?php echo esc_html( $editorial['title'] ); ?></h2>
					<p><?php echo esc_html( $editorial['text'] ); ?></p>
					<a class="ev-btn ev-btn--primary" href="<?php echo esc_url( $all_url ); ?>"><?php esc_html_e( 'Voir tous les produits', 'evasions' ); ?> <span aria-hidden="true">→</span></a>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( array() !== $products ) : ?>
	<section class="ev-section ev-uselection" aria-labelledby="ev-uselection-title">
		<div class="ev-container">
			<div class="ev-section__head">
				<h2 id="ev-uselection-title"><?php esc_html_e( 'Sélection de l’univers', 'evasions' ); ?></h2>
				<a class="ev-link" href="<?php echo esc_url( $all_url ); ?>"><?php esc_html_e( 'Tout voir', 'evasions' ); ?> →</a>
			</div>
			<div class="ev-grid ev-grid--products">
				<?php
				foreach ( $products as $product ) {
					get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );
				}
				?>
			</div>
		</div>
	</section>
<?php endif; ?>
