<?php
/**
 * One post in an archive loop.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

Beaver_Light_Hooks::fire( 'beaver_light_entry_before' );
?>
<article <?php post_class( 'blt-loop-item' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="blt-loop-item__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
	<?php endif; ?>
	<div class="blt-loop-item__body">
		<h2 class="blt-loop-item__title">
			<?php
			// Core's own condition for the sticky class, whose border is otherwise the only sign of a sticky post.
			if ( is_sticky() && is_home() && ! is_paged() ) {
				/* translators: Screen reader prefix to a sticky post's title. */
				printf( '<span class="screen-reader-text">%s</span>', esc_html__( 'Featured:', 'beaver-light' ) );
			}
			?>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_meta_open' ); ?>
		<?php beaver_light_entry_meta(); ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_meta_close' ); ?>
		<div class="blt-loop-item__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
<?php
Beaver_Light_Hooks::fire( 'beaver_light_entry_after' );
