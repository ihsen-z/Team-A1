<?php
/**
 * Listing produits : titre, compte, tri, filtres, grille, pagination.
 *
 * Reprend la boucle standard de WooCommerce (`archive-product.php`) avec ses
 * crochets d'origine, pour que notices et extensions continuent de s'y brancher.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$active = evasions_active_filters();
$total  = (int) wc_get_loop_prop( 'total', (int) $GLOBALS['wp_query']->found_posts );
?>
<div class="ev-container">
	<div class="ev-listing">
		<?php get_template_part( 'template-parts/listing-filters' ); ?>

		<div class="ev-listing__main">
			<div class="ev-listing__top">
				<header class="ev-listing__head">
					<h1><?php echo esc_html( evasions_listing_title() ); ?></h1>
					<p class="ev-listing__count"><?php echo esc_html( evasions_products_count_label( $total ) ); ?></p>
				</header>

				<div class="ev-listing__bar">
					<a class="ev-listing__filter" href="#ev-filters" role="button" aria-controls="ev-filters" aria-expanded="false" data-ev-filters-open>
						<?php
						echo esc_html__( 'Filtres', 'evasions' );
						echo array() !== $active ? ' (' . (int) count( $active ) . ')' : '';
						?>
					</a>
					<div class="ev-listing__sort">
						<span class="ev-listing__sort-label" aria-hidden="true"><?php esc_html_e( 'Trier par :', 'evasions' ); ?></span>
						<?php woocommerce_catalog_ordering(); ?>
					</div>
				</div>
			</div>

			<?php if ( array() !== $active ) : ?>
				<ul class="ev-chips" aria-label="<?php esc_attr_e( 'Filtres actifs', 'evasions' ); ?>">
					<?php foreach ( $active as $chip ) : ?>
						<li>
							<a class="ev-chip" href="<?php echo esc_url( $chip['url'] ); ?>">
								<?php echo esc_html( $chip['label'] ); ?>
								<span aria-hidden="true">×</span>
								<span class="screen-reader-text"><?php esc_html_e( 'Retirer ce filtre', 'evasions' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
					<li><a class="ev-chip-reset" href="<?php echo esc_url( evasions_reset_filters_url() ); ?>"><?php esc_html_e( 'Réinitialiser', 'evasions' ); ?></a></li>
				</ul>
			<?php endif; ?>

			<?php
			if ( woocommerce_product_loop() ) {
				do_action( 'woocommerce_before_shop_loop' );
				woocommerce_product_loop_start();

				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}

				woocommerce_product_loop_end();
				do_action( 'woocommerce_after_shop_loop' );
			} else {
				do_action( 'woocommerce_no_products_found' );
			}
			?>
		</div>
	</div>
</div>
