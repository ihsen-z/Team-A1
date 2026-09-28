<?php
/**
 * Page FAQ — maquette « EVASIONS Pages Infos ».
 *
 * En-tête centré, rubriques en pastilles (mobile) ou en colonne (desktop),
 * questions en accordéon, puis un bloc « pas trouvé votre réponse ? ».
 *
 * Les questions viennent du contenu de la page (`evasions_faq_groups()`) : un
 * `<h2>` par rubrique, un `<h3>` par question. Rien n'est écrit ici, donc rien
 * n'est inventé (§60) ; une page qui ne suit pas cette structure s'affiche
 * telle quelle. L'accordéon repose sur `<details>` : sans JavaScript, les
 * questions s'ouvrent quand même.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$groups   = evasions_faq_groups();
$has_tabs = count( $groups ) > 1;
$contact  = get_page_by_path( 'contact' );
?>
<div class="ev-info ev-info--faq">
	<div class="ev-container">
		<header class="ev-info__head ev-info__head--center">
			<p class="ev-info__over"><?php esc_html_e( 'Aide', 'evasions' ); ?></p>
			<h1 class="ev-info__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( array() === $groups ) : ?>
			<div class="ev-prose ev-info__prose"><?php echo wp_kses_post( evasions_page_content() ); ?></div>
		<?php else : ?>
			<div class="ev-faq<?php echo $has_tabs ? ' ev-faq--tabs' : ''; ?>" data-ev-filter-scope>
				<?php if ( $has_tabs ) : ?>
					<nav class="ev-faq__cats" aria-label="<?php esc_attr_e( 'Rubriques', 'evasions' ); ?>" data-ev-filter="faq">
						<button type="button" class="ev-chip is-active" data-ev-filter-value="all" aria-pressed="true"><?php esc_html_e( 'Toutes', 'evasions' ); ?></button>
						<?php foreach ( $groups as $index => $group ) : ?>
							<button type="button" class="ev-chip" data-ev-filter-value="g<?php echo (int) $index; ?>" aria-pressed="false"><?php echo esc_html( $group['title'] ); ?></button>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

				<div class="ev-faq__list" data-ev-accordion>
					<?php foreach ( $groups as $index => $group ) : ?>
						<?php if ( $has_tabs && '' !== $group['title'] ) : ?>
							<h2 class="ev-faq__group" data-ev-filter-item="g<?php echo (int) $index; ?>"><?php echo esc_html( $group['title'] ); ?></h2>
						<?php endif; ?>
						<?php foreach ( $group['items'] as $item ) : ?>
							<details class="ev-acc" data-ev-filter-item="g<?php echo (int) $index; ?>">
								<summary class="ev-acc__q">
									<span class="ev-acc__label"><?php echo esc_html( $item['q'] ); ?></span>
									<span class="ev-acc__sign" aria-hidden="true"></span>
								</summary>
								<div class="ev-acc__a ev-prose"><?php echo wp_kses_post( $item['a'] ); ?></div>
							</details>
						<?php endforeach; ?>
					<?php endforeach; ?>

					<?php if ( $contact instanceof WP_Post ) : ?>
						<aside class="ev-info__cta">
							<p class="ev-info__cta-title"><?php esc_html_e( 'Vous n’avez pas trouvé votre réponse ?', 'evasions' ); ?></p>
							<a class="ev-btn ev-btn--sun" href="<?php echo esc_url( get_permalink( $contact ) ); ?>"><?php esc_html_e( 'Nous contacter', 'evasions' ); ?></a>
						</aside>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
