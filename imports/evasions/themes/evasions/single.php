<?php
/**
 * Un article : titre, date, contenu complet.
 *
 * Sans lui, WordPress retombait sur `index.php`, qui n'affiche que l'extrait.
 * Le site est une boutique : pas de blog à mettre en scène, un gabarit sobre.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ev-container ev-content">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'ev-page' ); ?>>
			<h1 class="ev-content__title"><?php the_title(); ?></h1>
			<p class="ev-entry__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			<div class="ev-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>
<?php
get_footer();
