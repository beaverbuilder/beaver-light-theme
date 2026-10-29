<?php
/**
 * Template helpers. Intentionally few.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the post meta line: date, author, categories.
 *
 * @since 0.1.0
 * @return void
 */
function blt_entry_meta(): void {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	$categories = get_the_category_list( ', ' );

	echo '<p class="blt-entry-meta">';
	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	echo ' <span class="blt-sep" aria-hidden="true">&middot;</span> ';
	the_author_posts_link();

	if ( $categories ) {
		echo ' <span class="blt-sep" aria-hidden="true">&middot;</span> ' . wp_kses_post( $categories );
	}

	echo '</p>';
}

/**
 * The heading for archive-style views (blog page, search, archives).
 *
 * @since 0.1.0
 * @return string Heading text, may contain markup from get_the_archive_title().
 */
function blt_archive_title(): string {
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( __( 'Search results for &ldquo;%s&rdquo;', 'beaver-light-theme' ), esc_html( get_search_query() ) );
	}

	if ( is_home() ) {
		$is_static_front = ! is_front_page();

		return $is_static_front ? single_post_title( '', false ) : '';
	}

	return get_the_archive_title();
}

/**
 * Print the site branding: the custom logo, else the site title.
 *
 * The title is an h1 only on a front-page blog, where nothing else is.
 *
 * @since 0.1.0
 * @return void
 */
function blt_site_branding(): void {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$tag = ( is_front_page() && is_home() ) ? 'h1' : 'p';

	printf(
		'<%1$s class="blt-site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
		tag_escape( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}
