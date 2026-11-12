<?php
/**
 * Every list view: the blog, archives and search results.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="blt-container blt-container--narrow blt-archive">
	<?php get_template_part( 'parts/archive-header' ); ?>

	<?php if ( have_posts() ) : ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_loop_before' ); ?>
		<div class="blt-loop">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/loop-item' );
			endwhile;
			?>
		</div>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_loop_after' ); ?>
		<?php
		the_posts_pagination(
			array(
				'prev_text' => esc_html__( 'Newer', 'beaver-light' ),
				'next_text' => esc_html__( 'Older', 'beaver-light' ),
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part( 'parts/no-results' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
