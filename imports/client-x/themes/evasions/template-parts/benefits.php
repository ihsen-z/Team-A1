<?php
/**
 * Bandeau de réassurance : quatre arguments, une icône chacun.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$benefits = evasions_benefits();

if ( array() === $benefits ) {
	return;
}
?>
<section class="ev-benefits-wrap" aria-label="<?php esc_attr_e( 'Nos engagements', 'evasions' ); ?>">
	<div class="ev-container">
		<ul class="ev-benefits" style="--ev-cols:<?php echo (int) count( $benefits ); ?>">
			<?php foreach ( $benefits as $benefit ) : ?>
				<li class="ev-benefit">
					<span class="ev-benefit__icon"><?php echo evasions_icon( $benefit['icon'], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></span>
					<span class="ev-benefit__text">
						<strong><?php echo esc_html( $benefit['title'] ); ?></strong>
						<span><?php echo esc_html( $benefit['sub'] ); ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
