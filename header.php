<?php
/**
 * Document open: head, page frame, header slot, main content open.
 *
 * Hook order here is API; see Beaver_Light_Hooks::map().
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="blt-skip-link screen-reader-text" href="#blt-content"><?php esc_html_e( 'Skip to content', 'beaver-light' ); ?></a>
<div class="blt-page">
	<?php Beaver_Light_Hooks::fire( 'beaver_light_page_open' ); ?>

	<?php if ( Beaver_Light_Chrome::show_header_footer() ) : ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_header_before' ); ?>
		<?php
		// Native header renders here; Themer swaps the callback when a header layout applies.
		do_action( 'beaver_light_header' );
		?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_header_after' ); ?>
	<?php endif; ?>

	<?php Beaver_Light_Hooks::fire( 'beaver_light_content_before' ); ?>
	<main id="blt-content" class="blt-content <?php echo esc_attr( Beaver_Light_Design_System::page_class() ); ?>">
		<?php Beaver_Light_Hooks::fire( 'beaver_light_content_open' ); ?>
