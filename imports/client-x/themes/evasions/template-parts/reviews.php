<?php
/**
 * Avis de nos clients : uniquement de vrais avis WooCommerce.
 *
 * Sans avis publié, la section n'existe pas (§60) : le design montrait des
 * avis de remplissage, le site n'en affiche aucun. Sur mobile, les avis
 * défilent horizontalement avec accroche (scroll-snap), sans JavaScript.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$reviews = evasions_reviews( 3 );

if ( array() === $reviews ) {
	return;
}
?>
<section class="ev-section ev-reviews" aria-labelledby="ev-reviews-title">
	<div class="ev-container">
		<div class="ev-section__head">
			<h2 id="ev-reviews-title"><?php echo esc_html( evasions_home_section_title( 'reviews', __( 'Avis de nos clients', 'evasions' ) ) ); ?></h2>
		</div>

		<ul class="ev-reviews__list" style="--ev-cols:<?php echo (int) count( $reviews ); ?>">
			<?php foreach ( $reviews as $review ) : ?>
				<li class="ev-review">
					<?php echo evasions_stars( (float) $review['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup construit et échappé par evasions_stars(). ?>
					<blockquote>« <?php echo esc_html( $review['text'] ); ?> »</blockquote>
					<footer>
						<span class="ev-review__avatar" aria-hidden="true"></span>
						<a href="<?php echo esc_url( $review['url'] ); ?>"><?php echo esc_html( $review['author'] ); ?></a>
					</footer>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
