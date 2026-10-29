<?php
/**
 * Not found: a search form and a way home.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="blt-container blt-container--narrow blt-404">
	<h1 class="blt-archive-title"><?php esc_html_e( 'Page not found', 'beaver-light-theme' ); ?></h1>
	<div class="blt-prose">
		<p><?php esc_html_e( 'The page you were looking for is not here. Try a search, or head back to the home page.', 'beaver-light-theme' ); ?></p>
		<?php get_search_form(); ?>
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the home page', 'beaver-light-theme' ); ?></a></p>
	</div>
</div>
<?php
get_footer();
