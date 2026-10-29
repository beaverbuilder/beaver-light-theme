<?php
/**
 * Heading and description for blog, archive and search views.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

$blt_title       = blt_archive_title();
$blt_description = get_the_archive_description();

if ( '' === $blt_title ) {
	return;
}
?>
<header class="blt-archive-header">
	<h1 class="blt-archive-title"><?php echo wp_kses_post( $blt_title ); ?></h1>
	<?php if ( $blt_description ) : ?>
		<div class="blt-archive-description"><?php echo wp_kses_post( $blt_description ); ?></div>
	<?php endif; ?>
</header>
