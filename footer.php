<?php
/**
 * Document close: main content close, footer slot, page frame close.
 *
 * Mirrors header.php; hook order here is API.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;
?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_content_close' ); ?>
	</main>
	<?php Beaver_Light_Hooks::fire( 'beaver_light_content_after' ); ?>

	<?php if ( Beaver_Light_Chrome::show_header_footer() ) : ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_footer_before' ); ?>
		<?php
		// Native footer renders here; Themer swaps the callback when a footer layout applies.
		do_action( 'beaver_light_footer' );
		?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_footer_after' ); ?>
	<?php endif; ?>

	<?php Beaver_Light_Hooks::fire( 'beaver_light_page_close' ); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
