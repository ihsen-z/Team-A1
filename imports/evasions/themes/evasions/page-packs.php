<?php
/**
 * Page Packs — maquette « EVASIONS Pages Infos ».
 *
 * Bannière photo (surtitre, titre, phrase), pastilles de filtre, puis la
 * grille des packs.
 *
 * Un pack est un **produit** de la catégorie « packs » : c'est la boutique qui
 * porte son prix, sa photo et sa composition, pas le thème (§26). Les cartes
 * sont celles du reste du site. Sans catégorie « packs » ou sans produit
 * dedans, la page affiche son contenu rédigé : aucun pack n'est inventé (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$packs    = evasions_packs_products();
$products = $packs['products'];
$terms    = $packs['terms'];
// Extrait saisi à la main seulement : l'extrait automatique recopierait le
// début du contenu, y compris ses mentions « À COMPLÉTER ».
$excerpt  = trim( wp_strip_all_tags( (string) get_post_field( 'post_excerpt', get_queried_object_id() ) ) );
?>
<div class="ev-info ev-info--packs">
	<div class="ev-container">
		<header class="ev-packs-hero">
			<?php
			echo evasions_section_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image aux URL/attributs échappés.
				'packs',
				'banner-randonneur.webp',
				array(
					'img_class' => 'ev-packs-hero__img',
					'width'     => 1200,
					'height'    => 800,
					'loading'   => 'lazy',
					'decoding'  => 'async',
					'size'      => 'full',
				)
			);
			?>
			<div class="ev-packs-hero__shade" aria-hidden="true"></div>
			<div class="ev-packs-hero__copy">
				<p class="ev-info__over ev-info__over--sun"><?php echo esc_html( evasions_setting( 'evasions_packs_over' ) ); ?></p>
				<h1 class="ev-packs-hero__title"><?php the_title(); ?></h1>
				<?php if ( '' !== $excerpt ) : ?>
					<p class="ev-packs-hero__text"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( array() === $products ) : ?>
			<div class="ev-prose ev-info__prose"><?php echo wp_kses_post( evasions_page_content() ); ?></div>
		<?php else : ?>
			<div class="ev-packs" data-ev-filter-scope>
				<div class="ev-packs__bar">
					<?php if ( array() !== $terms ) : ?>
						<nav class="ev-faq__cats" aria-label="<?php esc_attr_e( 'Filtrer les packs', 'evasions' ); ?>" data-ev-filter="packs">
							<button type="button" class="ev-chip is-active" data-ev-filter-value="all" aria-pressed="true"><?php esc_html_e( 'Tous', 'evasions' ); ?></button>
							<?php foreach ( $terms as $term ) : ?>
								<button type="button" class="ev-chip" data-ev-filter-value="<?php echo esc_attr( $term->slug ); ?>" aria-pressed="false"><?php echo esc_html( $term->name ); ?></button>
							<?php endforeach; ?>
						</nav>
					<?php endif; ?>
					<?php // Les deux formes sont passées au script : il recompte après un filtre, sans dupliquer la traduction. ?>
					<p class="ev-packs__count" data-ev-count-one="<?php echo esc_attr__( '%d pack', 'evasions' ); ?>" data-ev-count-many="<?php echo esc_attr__( '%d packs', 'evasions' ); ?>">
						<?php
						printf(
							/* translators: %d: number of packs listed */
							esc_html( _n( '%d pack', '%d packs', count( $products ), 'evasions' ) ),
							(int) count( $products )
						);
						?>
					</p>
				</div>

				<div class="ev-packs__grid">
					<?php foreach ( $products as $product ) : ?>
						<div class="ev-packs__cell" data-ev-filter-item="<?php echo esc_attr( evasions_pack_terms( $product ) ); ?>">
							<?php
							get_template_part(
								'template-parts/product-card',
								null,
								array(
									'product'   => $product,
									'title_tag' => 'h2',
								)
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
