<?php
/**
 * A native (non-builder) singular entry: title, meta, image, content, footer.
 *
 * The header keeps BB's fl-post-header class so the builder's "Default Page
 * Heading" global setting can still hide it.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

$blt_is_post = 'post' === get_post_type();

BLT_Hooks::fire( 'blt_entry_before' );
?>
<article <?php post_class( 'blt-entry' ); ?>>
	<header class="blt-entry__header fl-post-header">
		<h1 class="blt-entry__title"><?php the_title(); ?></h1>
		<?php BLT_Hooks::fire( 'blt_entry_meta_open' ); ?>
		<?php blt_entry_meta(); ?>
		<?php BLT_Hooks::fire( 'blt_entry_meta_close' ); ?>
	</header>

	<?php if ( $blt_is_post && has_post_thumbnail() ) : ?>
		<figure class="blt-entry__thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
	<?php endif; ?>

	<?php BLT_Hooks::fire( 'blt_entry_content_before' ); ?>
	<div class="blt-entry__content blt-prose">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="blt-page-links">' . esc_html__( 'Pages:', 'beaver-light-theme' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>
	<?php BLT_Hooks::fire( 'blt_entry_content_after' ); ?>

	<footer class="blt-entry__footer">
		<?php BLT_Hooks::fire( 'blt_entry_footer_open' ); ?>
		<?php if ( $blt_is_post ) : ?>
			<?php the_tags( '<p class="blt-entry__tags">' . esc_html__( 'Tagged:', 'beaver-light-theme' ) . ' ', ', ', '</p>' ); ?>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="blt-post-nav__label">' . esc_html__( 'Previous', 'beaver-light-theme' ) . '</span> %title',
					'next_text' => '<span class="blt-post-nav__label">' . esc_html__( 'Next', 'beaver-light-theme' ) . '</span> %title',
				)
			);
			?>
		<?php endif; ?>
		<?php BLT_Hooks::fire( 'blt_entry_footer_close' ); ?>
	</footer>
</article>
<?php
BLT_Hooks::fire( 'blt_entry_after' );
