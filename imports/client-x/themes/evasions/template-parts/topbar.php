<?php
/**
 * Barre de promesses supérieure.
 *
 * Les promesses viennent du plugin (éditables en administration). Sur mobile,
 * seule la première est affichée : l'ordre décide de ce que voit un client.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$promises = evasions_promises();
$socials  = evasions_socials();

if ( array() === $promises && array() === $socials ) {
	return;
}
?>
<div class="ev-topbar">
	<div class="ev-container ev-topbar__inner">
		<ul class="ev-topbar__promises">
			<?php foreach ( $promises as $promise ) : ?>
				<li class="ev-topbar__item<?php echo $promise['accent'] ? ' is-accent' : ''; ?>">
					<span class="ev-topbar__icon">
						<?php if ( '' !== $promise['icon_url'] ) : ?>
							<img src="<?php echo esc_url( $promise['icon_url'] ); ?>" width="16" height="16" alt="" loading="lazy">
						<?php else : ?>
							<?php echo evasions_icon( $promise['icon'], 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG construit par evasions_icon(). ?>
						<?php endif; ?>
					</span>
					<span><?php echo esc_html( $promise['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( array() !== $socials ) : ?>
			<ul class="ev-social ev-social--top">
				<?php foreach ( $socials as $social ) : ?>
					<li>
						<a href="<?php echo esc_url( $social['url'] ); ?>" aria-label="<?php echo esc_attr( $social['label'] ); ?>" rel="noopener" target="_blank"><?php echo evasions_social_icon( $social['key'], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
