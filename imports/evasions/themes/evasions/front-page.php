<?php
/**
 * Homepage — maquette « EVASIONS Homepage », mobile 390 px et desktop 1440 px.
 *
 * Ordre du document directeur (§13) : accroche, deux univers, produits, bannière,
 * réassurance, preuves, communauté. Chaque bloc est une partie de gabarit ; un
 * bloc sans contenu réel (aucun produit, aucun avis) ne s'affiche pas.
 *
 * Le bandeau « Pourquoi choisir EVASIONS ? » a laissé la place au bloc
 * « communauté » (maquette « EVASIONS Newsletter ») : les mêmes arguments de
 * réassurance figurent déjà juste au-dessus (`benefits`).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/universes' );
get_template_part( 'template-parts/featured-products' );
get_template_part( 'template-parts/banner' );
get_template_part( 'template-parts/benefits' );
get_template_part( 'template-parts/reviews' );
get_template_part( 'template-parts/community' );

get_footer();
