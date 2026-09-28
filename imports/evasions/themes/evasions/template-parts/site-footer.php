<?php
/**
 * Pied de page : logo, liens, réseaux, mentions.
 *
 * Aucun lien mort : une page qui n'existe pas n'est pas listée (§22). Les
 * pictogrammes VISA / MC / D17 du design n'y figurent pas : la boutique
 * encaisse en espèces à la livraison, elle ne doit pas laisser croire à un
 * paiement en ligne qu'elle ne propose pas.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$columns = evasions_footer_columns();
$legal   = evasions_legal_links();
$socials = evasions_socials();
$items   = evasions_nav_items();
?>
<footer class="ev-footer">
	<div class="ev-container">
		<div class="ev-footer__grid">
			<div class="ev-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php echo esc_url( evasions_img( 'logo-full-white.svg' ) ); ?>" width="1142" height="750" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
				<p><?php echo esc_html( evasions_home_footer_tagline() ); ?></p>
			</div>

			<?php foreach ( $columns as $index => $column ) : ?>
				<nav class="ev-footer__col" aria-labelledby="ev-footer-col-<?php echo (int) $index; ?>">
					<h2 id="ev-footer-col-<?php echo (int) $index; ?>"><?php echo esc_html( $column['title'] ); ?></h2>
					<ul>
						<?php foreach ( $column['links'] as $link ) : ?>
							<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endforeach; ?>

			<nav class="ev-footer__col" aria-labelledby="ev-footer-universes">
				<h2 id="ev-footer-universes"><?php esc_html_e( 'Nos univers', 'evasions' ); ?></h2>
				<ul>
					<?php foreach ( $items as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<?php if ( array() !== $socials ) : ?>
				<div class="ev-footer__col ev-footer__follow">
					<h2><?php esc_html_e( 'Suivez-nous', 'evasions' ); ?></h2>
					<ul class="ev-social">
						<?php foreach ( $socials as $social ) : ?>
							<li><a href="<?php echo esc_url( $social['url'] ); ?>" aria-label="<?php echo esc_attr( $social['label'] ); ?>" rel="noopener" target="_blank"><?php echo evasions_social_icon( $social['key'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<div class="ev-footer__legal">
			<span>
				<?php
				/* translators: 1: year, 2: site name */
				echo esc_html( sprintf( __( '© %1$s %2$s. Tous droits réservés.', 'evasions' ), wp_date( 'Y' ), get_bloginfo( 'name' ) ) );
				?>
			</span>
			<?php if ( array() !== $legal ) : ?>
				<ul>
					<?php foreach ( $legal as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</footer>
