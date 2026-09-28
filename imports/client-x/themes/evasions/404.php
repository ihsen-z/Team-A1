<?php
/**
 * Page introuvable.
 *
 * Trois sorties pour ne pas laisser le visiteur dans une impasse : chercher un
 * produit, choisir un univers, voir la boutique. Les liens viennent des
 * catégories qui existent ; les produits proposés sont de vrais produits.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$universes = array();
foreach ( evasions_universes() as $slug => $universe ) {
	$link = get_term_link( $slug, 'product_cat' );

	// Un univers dont la catégorie n'existe pas n'a pas de page : on ne le propose pas.
	if ( ! is_wp_error( $link ) ) {
		$universes[] = array(
			'title' => $universe['title'],
			'url'   => (string) $link,
		);
	}
}
?>
<section class="ev-404">
	<div class="ev-container ev-404__inner">
		<p class="ev-404__code" aria-hidden="true">404</p>
		<h1><?php esc_html_e( 'Page introuvable', 'evasions' ); ?></h1>
		<p class="ev-404__text"><?php esc_html_e( 'Cette page n’existe pas ou n’existe plus. Cherchez un produit ou choisissez un univers.', 'evasions' ); ?></p>

		<div class="ev-404__search">
			<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'ev-search-404' ) ); ?>
		</div>

		<p class="ev-404__links">
			<?php foreach ( $universes as $universe ) : ?>
				<a class="ev-btn ev-btn--outline" href="<?php echo esc_url( $universe['url'] ); ?>"><?php echo esc_html( $universe['title'] ); ?></a>
			<?php endforeach; ?>
			<a class="ev-btn ev-btn--primary" href="<?php echo esc_url( evasions_shop_url() ); ?>"><?php esc_html_e( 'Voir la boutique', 'evasions' ); ?></a>
		</p>
	</div>
</section>

<?php get_template_part( 'template-parts/featured-products' ); ?>
<?php
get_footer();
