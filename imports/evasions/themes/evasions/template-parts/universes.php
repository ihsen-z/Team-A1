<?php
/**
 * Les deux univers : Plage, Camping & Randonnée (§7-8 du document directeur).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="ev-universes" aria-label="<?php echo esc_attr( evasions_home_section_title( 'universes', __( 'Nos univers', 'evasions' ) ) ); ?>">
	<div class="ev-container ev-universes__grid">
		<?php foreach ( evasions_universes() as $slug => $universe ) : ?>
			<a class="ev-universe" href="<?php echo esc_url( evasions_category_url( $slug ) ); ?>">
				<?php echo evasions_section_image( $universe['img_key'], $universe['image'], array( 'img_class' => 'ev-universe__img', 'width' => 612, 'height' => 408, 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image échappé. ?>
				<span class="ev-universe__shade" aria-hidden="true"></span>
				<span class="ev-universe__body">
					<span class="ev-universe__title"><?php echo esc_html( $universe['title'] ); ?></span>
					<span class="ev-universe__sub"><?php echo esc_html( $universe['sub'] ); ?></span>
					<span class="ev-btn ev-btn--white ev-universe__cta" aria-hidden="true"><?php esc_html_e( 'Découvrir', 'evasions' ); ?> →</span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
