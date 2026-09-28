<?php
/**
 * Page CGV — maquette « EVASIONS Pages Infos ».
 *
 * Fil d'Ariane, titre, date de mise à jour, puis les articles : sommaire
 * collant et cartes numérotées en desktop, accordéon en mobile.
 *
 * Les articles sont rendus dépliés : c'est l'état des cartes en desktop, et
 * celui des CGV sans JavaScript (un texte contractuel doit rester lisible).
 * En mobile, le script les replie et n'en laisse qu'un ouvert.
 *
 * Les articles viennent du contenu de la page (`evasions_cgv_articles()`) : un
 * `<h2>` par article. Le texte n'est pas touché ; le thème ne fait que le
 * découper et l'habiller. Le bandeau « points clés » de la maquette n'est pas
 * repris : ses libellés (délais, frais) ne sont pas encore confirmés (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cgv      = evasions_cgv_articles();
$articles = $cgv['articles'];
?>
<div class="ev-info ev-info--cgv">
	<div class="ev-container">
		<header class="ev-info__head">
			<nav class="ev-info__crumbs" aria-label="<?php esc_attr_e( 'Fil d’Ariane', 'evasions' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'evasions' ); ?></a>
				<span aria-hidden="true">›</span>
				<span><?php the_title(); ?></span>
			</nav>
			<h1 class="ev-info__title"><?php the_title(); ?></h1>
			<p class="ev-info__date">
				<?php
				printf(
					/* translators: %s: date of the last update of the page */
					esc_html__( 'Dernière mise à jour : %s', 'evasions' ),
					esc_html( get_the_modified_date() )
				);
				?>
			</p>
			<?php if ( '' !== trim( wp_strip_all_tags( $cgv['intro'] ) ) ) : ?>
				<div class="ev-info__note ev-prose"><?php echo wp_kses_post( $cgv['intro'] ); ?></div>
			<?php endif; ?>
		</header>

		<?php if ( array() === $articles ) : ?>
			<div class="ev-prose ev-info__prose"><?php echo wp_kses_post( evasions_page_content() ); ?></div>
		<?php else : ?>
			<div class="ev-cgv">
				<nav class="ev-cgv__toc" aria-label="<?php esc_attr_e( 'Sommaire', 'evasions' ); ?>">
					<p class="ev-cgv__toc-title"><?php esc_html_e( 'Sommaire', 'evasions' ); ?></p>
					<?php foreach ( $articles as $article ) : ?>
						<a href="#<?php echo esc_attr( $article['anchor'] ); ?>">
							<?php echo evasions_icon( evasions_cgv_icon( $article['title'] ), 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
							<span><?php echo esc_html( $article['title'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>

				<div class="ev-cgv__list" data-ev-accordion>
					<?php foreach ( $articles as $article ) : ?>
						<?php // <details open> : déplié en desktop par la feuille de style, replié en mobile (accordéon). ?>
						<details class="ev-acc ev-cgv__item" id="<?php echo esc_attr( $article['anchor'] ); ?>" open>
							<summary class="ev-acc__q ev-cgv__head">
								<span class="ev-cgv__icon">
									<?php echo evasions_icon( evasions_cgv_icon( $article['title'] ), 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?>
									<span class="ev-cgv__num" aria-hidden="true"><?php echo esc_html( (string) $article['num'] ); ?></span>
								</span>
								<span class="ev-cgv__titles">
									<span class="ev-cgv__over">
										<?php
										printf(
											/* translators: %s: two-digit article number */
											esc_html__( 'Article %s', 'evasions' ),
											esc_html( str_pad( (string) $article['num'], 2, '0', STR_PAD_LEFT ) )
										);
										?>
									</span>
									<span class="ev-cgv__name"><?php echo esc_html( $article['title'] ); ?></span>
								</span>
								<span class="ev-acc__sign" aria-hidden="true"></span>
							</summary>
							<div class="ev-acc__a ev-prose"><?php echo wp_kses_post( $article['html'] ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
