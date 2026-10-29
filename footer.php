<?php
/**
 * Document close: main content close, footer slot, page frame close.
 *
 * Mirrors header.php; hook order here is API.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
		<?php BLT_Hooks::fire( 'blt_content_close' ); ?>
	</main>
	<?php BLT_Hooks::fire( 'blt_content_after' ); ?>

	<?php if ( BLT_Chrome::show_header_footer() ) : ?>
		<?php BLT_Hooks::fire( 'blt_footer_before' ); ?>
		<?php
		// Native footer renders here; Themer swaps the callback when a footer layout applies.
		do_action( 'blt_footer' );
		?>
		<?php BLT_Hooks::fire( 'blt_footer_after' ); ?>
	<?php endif; ?>

	<?php BLT_Hooks::fire( 'blt_page_close' ); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
