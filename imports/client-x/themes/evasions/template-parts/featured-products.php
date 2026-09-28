<?php
/**
 * Produits mis en avant : quatre produits WooCommerce réels.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$products = evasions_featured_products( 4 );

if ( array() === $products ) {
	return;
}
?>
<section class="ev-section ev-featured" aria-labelledby="ev-featured-title">
	<div class="ev-container">
		<div class="ev-section__head">
			<h2 id="ev-featured-title"><?php echo esc_html( evasions_home_section_title( 'featured', __( 'Nos produits mis en avant', 'evasions' ) ) ); ?></h2>
			<a class="ev-link" href="<?php echo esc_url( evasions_shop_url() ); ?>"><?php esc_html_e( 'Voir tous', 'evasions' ); ?> →</a>
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
