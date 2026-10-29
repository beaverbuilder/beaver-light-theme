<?php
/**
 * One post in an archive loop.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

BLT_Hooks::fire( 'blt_entry_before' );
?>
<article <?php post_class( 'blt-loop-item' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="blt-loop-item__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
	<?php endif; ?>
	<div class="blt-loop-item__body">
		<h2 class="blt-loop-item__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php BLT_Hooks::fire( 'blt_entry_meta_open' ); ?>
		<?php blt_entry_meta(); ?>
		<?php BLT_Hooks::fire( 'blt_entry_meta_close' ); ?>
		<div class="blt-loop-item__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
<?php
BLT_Hooks::fire( 'blt_entry_after' );
