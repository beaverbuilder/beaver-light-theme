<?php
/**
 * Document open: head, page frame, header slot, main content open.
 *
 * Hook order here is API; see BLT_Hooks::map().
 *
 * @package Beaver_Light_Theme
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
<a class="blt-skip-link screen-reader-text" href="#blt-content"><?php esc_html_e( 'Skip to content', 'beaver-light-theme' ); ?></a>
<div class="blt-page">
	<?php BLT_Hooks::fire( 'blt_page_open' ); ?>

	<?php if ( BLT_Chrome::show_header_footer() ) : ?>
		<?php BLT_Hooks::fire( 'blt_header_before' ); ?>
		<?php
		// Native header renders here; Themer swaps the callback when a header layout applies.
		do_action( 'blt_header' );
		?>
		<?php BLT_Hooks::fire( 'blt_header_after' ); ?>
	<?php endif; ?>

	<?php BLT_Hooks::fire( 'blt_content_before' ); ?>
	<main id="blt-content" class="blt-content <?php echo esc_attr( BLT_Design_System::page_class() ); ?>">
		<?php BLT_Hooks::fire( 'blt_content_open' ); ?>
