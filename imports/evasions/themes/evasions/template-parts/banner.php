<?php
/**
 * Bannière « pensés pour les vrais aventuriers ».
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$banner = evasions_home_banner();

if ( '' === trim( $banner['title'] ) && '' === trim( $banner['text'] ) ) {
	return;
}
?>
<section class="ev-banner-wrap" aria-labelledby="ev-banner-title">
	<div class="ev-container">
		<div class="ev-banner">
			<?php echo evasions_section_image( 'banner', 'banner-randonneur.webp', array( 'img_class' => 'ev-banner__img', 'width' => 612, 'height' => 408, 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image aux URL/attributs échappés. ?>
			<div class="ev-banner__shade" aria-hidden="true"></div>
			<div class="ev-banner__copy">
				<?php if ( '' !== trim( $banner['title'] ) ) : ?>
					<h2 id="ev-banner-title"><?php echo esc_html( $banner['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== trim( $banner['text'] ) ) : ?>
					<p><?php echo esc_html( $banner['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( $banner['cta'] ) ) : ?>
					<a class="ev-btn ev-btn--sun" href="<?php echo esc_url( evasions_shop_url() ); ?>">
						<?php echo esc_html( $banner['cta'] ); ?> <span class="ev-btn__arrow" aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
