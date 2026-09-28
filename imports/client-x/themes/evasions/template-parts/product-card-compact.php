<?php
/**
 * Carte produit compacte : « Produits complémentaires » de la fiche produit.
 *
 * Même source de données que la carte de la homepage, sans badge ni note, avec un
 * bouton « + Ajouter » en contour (la maquette réserve le bouton plein à l'achat
 * du produit principal).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;

if ( ! $product instanceof WC_Product ) {
	return;
}

$link = $product->get_permalink();

global $post;
$previous_post = $post;
$post          = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restauré en fin de carte.
setup_postdata( $post );
$GLOBALS['product'] = $product;
?>
<article class="ev-card ev-card--compact">
	<a class="ev-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
		<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup d'image WooCommerce. ?>
	</a>
	<div class="ev-card__body">
		<h3 class="ev-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<div class="ev-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php woocommerce_template_loop_add_to_cart( array( 'ev_compact' => true ) ); ?>
	</div>
</article>
<?php
wp_reset_postdata();
$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restauration.
