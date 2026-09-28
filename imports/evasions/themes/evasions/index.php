<?php
/**
 * Gabarit de repli : articles, archives, résultats de recherche.
 *
 * Volontairement sobre. Le site est une boutique : les gabarits qui comptent
 * sont la homepage (front-page.php) et ceux de WooCommerce (woocommerce.php).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ev-container ev-content">
	<?php if ( have_posts() ) : ?>
		<?php if ( is_search() ) : ?>
			<h1 class="ev-content__title">
				<?php
				/* translators: %s: search query */
				printf( esc_html__( 'Résultats pour « %s »', 'evasions' ), esc_html( get_search_query( false ) ) );
				?>
			</h1>
		<?php endif; ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'ev-entry' ); ?>>
				<h2 class="ev-entry__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="ev-entry__summary"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<h1 class="ev-content__title"><?php esc_html_e( 'Aucun résultat', 'evasions' ); ?></h1>
		<p><?php esc_html_e( 'Rien ne correspond à cette demande. Essayez un autre mot, ou parcourez la boutique.', 'evasions' ); ?></p>
		<div class="ev-404__search"><?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'ev-search-empty' ) ); ?></div>
		<p><a class="ev-btn ev-btn--primary" href="<?php echo esc_url( evasions_shop_url() ); ?>"><?php esc_html_e( 'Voir la boutique', 'evasions' ); ?></a></p>
	<?php endif; ?>
</div>
<?php
get_footer();
