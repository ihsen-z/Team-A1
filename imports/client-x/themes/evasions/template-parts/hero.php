<?php
/**
 * Hero : accroche, deux appels à l'action.
 *
 * Deux photos (mobile, desktop) servies par <picture> : le navigateur ne
 * télécharge que celle qu'il affiche. L'image est chargée en priorité, car
 * c'est l'élément le plus grand du premier écran (Largest Contentful Paint).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$hero = evasions_hero();

/*
 * La liste des photos (téléversées ou non, AVIF ou WebP, mobile ou desktop)
 * est construite par `evasions_hero_media()`, et non plus ici : le `<head>`
 * précharge la première image de cette même liste (audit PERF-02), et deux
 * listes finiraient par désigner deux images différentes — donc deux
 * téléchargements. Le dernier élément est l'`<img>` de repli.
 */
$hero_sources = evasions_hero_media();
$hero_img     = array_pop( $hero_sources );
?>
<section class="ev-hero" aria-labelledby="ev-hero-title">
	<picture class="ev-hero__media">
		<?php
		foreach ( $hero_sources as $hero_source ) {
			$hero_attrs  = '' !== $hero_source['type'] ? ' type="' . esc_attr( $hero_source['type'] ) . '"' : '';
			$hero_attrs .= '' !== $hero_source['media'] ? ' media="' . esc_attr( $hero_source['media'] ) . '"' : '';
			$hero_attrs .= '' !== $hero_source['sizes'] ? ' sizes="' . esc_attr( $hero_source['sizes'] ) . '"' : '';

			/*
			 * Le srcset responsive quand la photo est téléversée, l'URL seule
			 * sinon. Il doit rester IDENTIQUE à celui du préchargement : deux
			 * listes différentes feraient télécharger deux images au lieu d'une,
			 * ce que le préchargement était censé éviter.
			 */
			$hero_srcset = '' !== $hero_source['srcset'] ? $hero_source['srcset'] : esc_url( $hero_source['url'] );

			echo '<source' . $hero_attrs . ' srcset="' . esc_attr( $hero_srcset ) . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributs échappés un à un juste au-dessus.
		}
		?>
		<?php
		/*
		 * L'`<img>` porte le même srcset que le préchargement, et pour la même
		 * raison : sans lui, le navigateur préchargeait la sous-taille choisie
		 * par le srcset PUIS téléchargeait le fichier entier désigné par `src`.
		 * Mesuré à 390 px le 28 septembre 2026 : 170 Ko + 461 Ko au lieu de
		 * 170 Ko. Un préchargement qui ne désigne pas exactement la même image
		 * que l'élément rendu coûte le double de ce qu'il fait gagner.
		 */
		$hero_img_attrs = '';
		if ( '' !== $hero_img['srcset'] ) {
			$hero_img_attrs = ' srcset="' . esc_attr( $hero_img['srcset'] ) . '"';

			if ( '' !== $hero_img['sizes'] ) {
				$hero_img_attrs .= ' sizes="' . esc_attr( $hero_img['sizes'] ) . '"';
			}
		}

		echo '<img src="' . esc_url( $hero_img['url'] ) . '"' . $hero_img_attrs . ' width="612" height="408" alt="" fetchpriority="high" decoding="async">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributs échappés un à un juste au-dessus.
		?>
	</picture>
	<div class="ev-hero__shade" aria-hidden="true"></div>

	<div class="ev-container ev-hero__inner">
		<div class="ev-hero__copy">
			<h1 id="ev-hero-title">
				<?php echo esc_html( $hero['title_lead'] ); ?>
				<?php if ( '' !== $hero['title_em'] ) : ?>
					<em><?php echo esc_html( $hero['title_em'] ); ?></em>
				<?php endif; ?>
			</h1>
			<?php if ( '' !== $hero['subtitle'] ) : ?>
				<p><?php echo esc_html( $hero['subtitle'] ); ?></p>
			<?php endif; ?>
			<div class="ev-hero__cta">
				<a class="ev-btn ev-btn--sun" href="<?php echo esc_url( evasions_shop_url() ); ?>">
					<?php echo esc_html( $hero['cta_primary'] ); ?>
					<span class="ev-btn__arrow" aria-hidden="true">→</span>
				</a>
				<a class="ev-btn ev-btn--ghost" href="<?php echo esc_url( evasions_category_url( 'camping-randonnee' ) ); ?>">
					<?php echo esc_html( $hero['cta_secondary'] ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
