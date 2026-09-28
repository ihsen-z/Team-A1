<?php
/**
 * En-tête : logo, compte, panier, navigation, recherche dans le menu.
 *
 * La recherche vit dans le menu (§3.3) : sur mobile elle apparaît quand le
 * tiroir s'ouvre, sur desktop en tête de la barre de navigation. La ligne du
 * haut ne porte donc plus que le logo, le compte et le panier.
 *
 * Sur tout le tunnel — panier, commande, confirmation — l'en-tête est réduit au
 * logo et à la mention « Commande sécurisée » (§3, §9.1) : ni menu, ni
 * recherche, ni panier, pour ne rien mettre entre le client et sa commande.
 *
 * Pourquoi le panier et la confirmation en font partie, alors que la décision
 * initiale ne visait que la page Commander : la maquette « Fiche Produit &
 * Tunnel » traite les trois écrans comme un seul parcours, et la raison d'être
 * de l'en-tête allégé — retirer les sorties avant le formulaire — vaut dès le
 * panier. Sur le panier, l'icône du panier montrerait de toute façon un
 * compteur pour une page déjà sous les yeux du client.
 *
 * La mention de sécurité est un mot de réassurance, pas une affirmation
 * technique : elle dit que la commande est sécurisée, pas que « les
 * informations sont cryptées » — cette dernière formule de la maquette n'est
 * pas reprise, faute de pouvoir la garantir (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

// En-tête allégé : les trois écrans du tunnel (panier, commande, confirmation).
$minimal = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );

if ( $minimal ) :
	?>
	<header class="ev-header ev-header--minimal">
		<div class="ev-container ev-header__row ev-header__row--minimal">
			<a class="ev-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo esc_url( evasions_img( 'logo-compact.svg' ) ); ?>" width="1729" height="340" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>
			<p class="ev-header__secure">
				<?php echo evasions_icon( 'lock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
				<span><?php esc_html_e( 'Commande sécurisée', 'evasions' ); ?></span>
			</p>
		</div>
	</header>
	<?php
	return;
endif;

$has_wc = evasions_has_woocommerce();
$items  = evasions_nav_items();
?>
<header class="ev-header">
	<div class="ev-container ev-header__row">
		<button type="button" class="ev-burger" aria-expanded="false" aria-controls="ev-nav">
			<?php echo evasions_icon( 'menu', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'evasions' ); ?></span>
		</button>

		<a class="ev-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( evasions_img( 'logo-compact.svg' ) ); ?>" width="1729" height="340" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		</a>

		<div class="ev-tools">
			<?php if ( $has_wc ) : ?>
				<a class="ev-tool ev-tool--account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Mon compte', 'evasions' ); ?>">
					<?php echo evasions_icon( 'user', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
				</a>
				<a class="ev-tool ev-tool--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Panier', 'evasions' ); ?>">
					<?php echo evasions_icon( 'bag', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
					<?php echo evasions_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup construit par evasions_cart_count_html() : entiers uniquement. ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<nav class="ev-nav" id="ev-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'evasions' ); ?>">
		<div class="ev-container ev-nav__inner">
			<div class="ev-nav__search">
				<?php get_template_part( 'template-parts/search-form' ); ?>
			</div>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'ev-nav__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			} else {
				?>
				<ul class="ev-nav__list">
					<li class="<?php echo is_front_page() ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'evasions' ); ?></a></li>
					<?php foreach ( $items as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php } ?>
		</div>
	</nav>

	<div class="ev-pills" aria-label="<?php esc_attr_e( 'Accès rapide', 'evasions' ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<?php if ( $item['pill'] ) : ?>
				<a class="ev-pill" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</header>
<?php
// Sur le checkout, la barre de promesses supérieure est aussi masquée : voir header.php.
