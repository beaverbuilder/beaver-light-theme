<?php
/**
 * Comments list and form.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}

$blt_count = get_comments_number();
?>
<section id="comments" class="blt-comments">
	<?php BLT_Hooks::fire( 'blt_comments_open' ); ?>

	<?php if ( have_comments() ) : ?>
		<h2 class="blt-comments__title">
			<?php
			/* translators: %s: number of comments. */
			echo esc_html( sprintf( _n( '%s comment', '%s comments', $blt_count, 'beaver-light-theme' ), number_format_i18n( $blt_count ) ) );
			?>
		</h2>
		<ol class="blt-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && $blt_count ) : ?>
		<p class="blt-comments__closed"><?php esc_html_e( 'Comments are closed.', 'beaver-light-theme' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>

	<?php BLT_Hooks::fire( 'blt_comments_close' ); ?>
</section>
