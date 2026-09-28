<?php
/**
 * Champ de recherche de produits (en-tête, page 404).
 *
 * Pilule arrondie, sur fond blanc, avec un bouton d'envoi rond côté accent :
 * la même forme que les boutons du thème, et une cible visible pour le doigt
 * (la touche « Entrée » seule ne se devine pas sur mobile).
 *
 * `$args['id']` : identifiant du champ. Il doit être unique dans la page : la
 * page 404 en affiche un second, en plus de celui de l'en-tête.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$field_id = isset( $args['id'] ) ? sanitize_html_class( (string) $args['id'] ) : 'ev-search-field';
?>
<form class="ev-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Rechercher un produit', 'evasions' ); ?></label>
	<span class="ev-search__icon"><?php echo evasions_icon( 'search', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></span>
	<input id="<?php echo esc_attr( $field_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un produit…', 'evasions' ); ?>">
	<button class="ev-search__submit" type="submit" aria-label="<?php esc_attr_e( 'Rechercher', 'evasions' ); ?>">
		<?php echo evasions_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
	</button>
	<input type="hidden" name="post_type" value="product">
</form>
