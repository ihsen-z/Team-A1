<?php
/**
 * Rendu du bloc factory/cta-band.
 *
 * @package FactoryCore
 *
 * @var array $attributes Attributs du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$message  = isset($attributes['message']) ? (string) $attributes['message'] : '';
$cta_txt  = isset($attributes['ctaTexte']) ? (string) $attributes['ctaTexte'] : '';
$cta_url  = isset($attributes['ctaUrl']) ? (string) $attributes['ctaUrl'] : '';
$variante = isset($attributes['variante']) ? (string) $attributes['variante'] : 'primaire';

if ('' === $message || '' === $cta_txt || '' === $cta_url) {
	return '';
}

$variantes = array( 'primaire', 'accent', 'sobre' );
if (! in_array($variante, $variantes, true)) {
	$variante = 'primaire';
}

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'factory-cta factory-cta--' . sanitize_html_class($variante) )
);
?>
<aside <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>>
	<p class="factory-cta__message"><?php echo esc_html($message); ?></p>
	<a class="wp-block-button__link factory-cta__bouton" href="<?php echo esc_url($cta_url); ?>">
		<?php echo esc_html($cta_txt); ?>
	</a>
</aside>
