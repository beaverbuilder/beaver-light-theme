<?php
/**
 * Shown when a loop has nothing to list.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="blt-no-results blt-prose">
	<p>
		<?php
		if ( is_search() ) {
			esc_html_e( 'Nothing matched your search. Try different words.', 'beaver-light' );
		} else {
			esc_html_e( 'There is nothing here yet.', 'beaver-light' );
		}
		?>
	</p>
	<?php get_search_form(); ?>
</div>
