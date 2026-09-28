<?php
/**
 * Pages simples : À propos, Contact, Livraison… et les pages WooCommerce en shortcode.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_shop_flow = function_exists( 'evasions_is_cart_or_checkout' ) && evasions_is_cart_or_checkout();

// Panier et checkout : bandeau photo à la place du titre simple (maquettes « Panier » et « Checkout »).
if ( $is_shop_flow ) {
	get_template_part( 'template-parts/page-banner' );
}
?>
<div class="ev-container ev-content<?php echo $is_shop_flow ? ' ev-content--flow' : ''; ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'ev-page' ); ?>>
			<?php if ( ! $is_shop_flow ) : ?>
				<h1 class="ev-content__title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<div class="ev-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>
<?php
get_footer();
