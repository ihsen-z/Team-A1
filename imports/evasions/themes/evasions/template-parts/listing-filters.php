<?php
/**
 * Colonne de filtres du listing : sidebar en desktop, panneau plein écran en mobile.
 *
 * Un formulaire GET. Le panneau s'ouvre par `:target` (lien `#ev-filters`), donc
 * sans JavaScript ; le script du thème ajoute la gestion du focus, d'Échap et
 * l'envoi automatique en desktop. Chaque groupe vient d'une donnée réelle
 * (voir `inc/listing.php`).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
$get      = wp_unslash( $_GET );
$bounds   = evasions_price_bounds();
$groups   = evasions_attribute_filters();
$cats     = evasions_category_links();
$active   = evasions_active_filters();
$action   = remove_query_arg( array_keys( $_GET ), get_pagenum_link( 1, false ) );
$in_stock = ! empty( $get['en_stock'] );
$carry    = evasions_carried_params();
// phpcs:enable

if ( null !== $bounds ) {
	$low  = isset( $get['min_price'] ) ? max( $bounds['min'], min( $bounds['max'], (int) $get['min_price'] ) ) : $bounds['min'];
	$high = isset( $get['max_price'] ) ? min( $bounds['max'], max( $bounds['min'], (int) $get['max_price'] ) ) : $bounds['max'];
	$span = $bounds['max'] - $bounds['min'];

	// Gabarit du prix pour le script : « {n} TND ».
	$price_format = html_entity_decode( sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), '{n}' ), ENT_QUOTES, 'UTF-8' );
}
?>
<aside id="ev-filters" class="ev-filters" aria-labelledby="ev-filters-title">
	<form class="ev-filters__form" method="get" action="<?php echo esc_url( $action ); ?>">
		<div class="ev-filters__head">
			<h2 id="ev-filters-title"><?php esc_html_e( 'Filtres', 'evasions' ); ?></h2>
			<a class="ev-filters__close" href="#content" data-ev-filters-close>
				<span aria-hidden="true">×</span><span class="screen-reader-text"><?php esc_html_e( 'Fermer les filtres', 'evasions' ); ?></span>
			</a>
		</div>

		<div class="ev-filters__body">
			<?php foreach ( $carry as $name => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<?php endforeach; ?>
			<input type="hidden" name="ev_submit" value="1">

			<?php if ( null !== $bounds ) : ?>
				<fieldset class="ev-fgroup">
					<legend><?php esc_html_e( 'Prix', 'evasions' ); ?></legend>
					<div
						class="ev-range"
						data-ev-range
						data-format="<?php echo esc_attr( $price_format ); ?>"
						style="--lo:<?php echo esc_attr( (string) round( ( $low - $bounds['min'] ) / $span * 100, 2 ) ); ?>%;--hi:<?php echo esc_attr( (string) round( ( $high - $bounds['min'] ) / $span * 100, 2 ) ); ?>%"
					>
						<span class="ev-range__track" aria-hidden="true"></span>
						<input type="range" name="min_price" min="<?php echo (int) $bounds['min']; ?>" max="<?php echo (int) $bounds['max']; ?>" step="1" value="<?php echo (int) $low; ?>" aria-label="<?php esc_attr_e( 'Prix minimum', 'evasions' ); ?>">
						<input type="range" name="max_price" min="<?php echo (int) $bounds['min']; ?>" max="<?php echo (int) $bounds['max']; ?>" step="1" value="<?php echo (int) $high; ?>" aria-label="<?php esc_attr_e( 'Prix maximum', 'evasions' ); ?>">
					</div>
					<div class="ev-range__values">
						<span data-ev-range-lo><?php echo esc_html( evasions_plain_price( $low ) ); ?></span>
						<span data-ev-range-hi><?php echo esc_html( evasions_plain_price( $high ) ); ?></span>
					</div>
				</fieldset>
			<?php endif; ?>

			<?php if ( array() !== $cats ) : ?>
				<nav class="ev-fgroup" aria-labelledby="ev-fg-cats">
					<h3 id="ev-fg-cats"><?php esc_html_e( 'Catégories', 'evasions' ); ?></h3>
					<ul class="ev-fgroup__links">
						<?php foreach ( $cats as $cat ) : ?>
							<li><a href="<?php echo esc_url( $cat['url'] ); ?>"><?php echo esc_html( $cat['name'] ); ?> <span>(<?php echo (int) $cat['count']; ?>)</span></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<fieldset class="ev-fgroup">
				<legend><?php esc_html_e( 'Disponibilité', 'evasions' ); ?></legend>
				<label class="ev-check">
					<input type="checkbox" name="en_stock" value="1" <?php checked( $in_stock ); ?>>
					<span><?php esc_html_e( 'En stock', 'evasions' ); ?></span>
				</label>
			</fieldset>

			<?php foreach ( $groups as $group ) : ?>
				<fieldset class="ev-fgroup">
					<legend><?php echo esc_html( $group['label'] ); ?></legend>
					<?php foreach ( $group['terms'] as $term ) : ?>
						<label class="ev-check">
							<input type="checkbox" name="ev_f[<?php echo esc_attr( $group['slug'] ); ?>][]" value="<?php echo esc_attr( $term['slug'] ); ?>" <?php checked( $term['checked'] ); ?>>
							<span><?php echo esc_html( $term['name'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
		</div>

		<div class="ev-filters__foot">
			<button type="submit" class="ev-btn ev-btn--primary ev-filters__apply"><?php esc_html_e( 'Appliquer les filtres', 'evasions' ); ?></button>
			<?php if ( array() !== $active ) : ?>
				<a class="ev-filters__reset" href="<?php echo esc_url( evasions_reset_filters_url() ); ?>"><span aria-hidden="true">↺</span> <?php esc_html_e( 'Réinitialiser les filtres', 'evasions' ); ?></a>
			<?php endif; ?>
		</div>
	</form>
</aside>
