<?php
/**
 * A native (non-builder) singular entry: title, meta, image, content, footer.
 *
 * The header keeps BB's fl-post-header class so the builder's "Default Page
 * Heading" global setting can still hide it.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

$beaver_light_is_post = 'post' === get_post_type();

Beaver_Light_Hooks::fire( 'beaver_light_entry_before' );
?>
<article <?php post_class( 'blt-entry' ); ?>>
	<header class="blt-entry__header fl-post-header">
		<h1 class="blt-entry__title"><?php the_title(); ?></h1>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_meta_open' ); ?>
		<?php beaver_light_entry_meta(); ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_meta_close' ); ?>
	</header>

	<?php if ( $beaver_light_is_post && has_post_thumbnail() ) : ?>
		<figure class="blt-entry__thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
	<?php endif; ?>

	<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_content_before' ); ?>
	<div class="blt-entry__content blt-prose">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="blt-page-links">' . esc_html__( 'Pages:', 'beaver-light' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>
	<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_content_after' ); ?>

	<footer class="blt-entry__footer">
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_footer_open' ); ?>
		<?php if ( $beaver_light_is_post ) : ?>
			<?php the_tags( '<p class="blt-entry__tags">' . esc_html__( 'Tagged:', 'beaver-light' ) . ' ', ', ', '</p>' ); ?>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="blt-post-nav__label">' . esc_html__( 'Previous', 'beaver-light' ) . '</span> %title',
					'next_text' => '<span class="blt-post-nav__label">' . esc_html__( 'Next', 'beaver-light' ) . '</span> %title',
				)
			);
			?>
		<?php endif; ?>
		<?php Beaver_Light_Hooks::fire( 'beaver_light_entry_footer_close' ); ?>
	</footer>
</article>
<?php
Beaver_Light_Hooks::fire( 'beaver_light_entry_after' );
