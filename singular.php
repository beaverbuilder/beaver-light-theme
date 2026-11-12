<?php
/**
 * Posts and pages. A Beaver Builder layout gets the full width and no theme
 * title; native content gets a reading measure.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	if ( Beaver_Light_Chrome::is_builder_page() ) :
		Beaver_Light_Hooks::fire( 'beaver_light_entry_before' );
		?>
		<article <?php post_class( 'blt-builder-entry' ); ?>>
			<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_content_before' ); ?>
			<?php the_content(); ?>
			<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_content_after' ); ?>
		</article>
		<?php
		Beaver_Light_Hooks::fire( 'beaver_light_entry_after' );
	else :
		?>
		<div class="blt-container blt-container--narrow">
			<?php get_template_part( 'parts/entry' ); ?>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
		<?php
	endif;
endwhile;

get_footer();
