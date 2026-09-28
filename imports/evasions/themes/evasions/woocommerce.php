<?php
/**
 * Gabarit des pages WooCommerce : boutique, catégories, fiche produit.
 *
 * WooCommerce l'utilise à la place de `index.php` pour ses archives et ses
 * fiches. Chaque écran a sa mise en page :
 * - fiche produit : `inc/product.php` et `assets/css/product.css` ;
 * - listing (boutique, catégorie, recherche) : `inc/listing.php` ;
 * - univers (catégorie de premier niveau) : `inc/universe.php`.
 * Le reste (compte client, page de remerciement) affiche le contenu natif de
 * WooCommerce dans l'en-tête et le pied de page du thème.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ev-container ev-woo-crumbs">
	<?php woocommerce_breadcrumb(); ?>
</div>

<?php if ( function_exists( 'is_product' ) && is_product() ) : ?>
	<div class="ev-product-page">
		<?php woocommerce_content(); ?>
	</div>
	<?php get_template_part( 'template-parts/product-cta' ); // Bandeau « appel à l'aventure », juste avant le pied de page. ?>
<?php elseif ( evasions_is_universe() ) : ?>
	<?php get_template_part( 'template-parts/universe' ); ?>
	<?php evasions_listing_footer_band(); ?>
<?php elseif ( evasions_is_listing() ) : ?>
	<?php get_template_part( 'template-parts/listing' ); ?>
	<?php evasions_listing_footer_band(); ?>
<?php else : ?>
	<div class="ev-container ev-content ev-woo">
		<?php woocommerce_content(); ?>
	</div>
<?php endif; ?>
<?php
get_footer();
