<?php
/**
 * Heading and description for blog, archive and search views.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

$beaver_light_title       = beaver_light_archive_title();
$beaver_light_description = get_the_archive_description();

if ( '' === $beaver_light_title ) {
	return;
}
?>
<header class="blt-archive-header">
	<h1 class="blt-archive-title"><?php echo wp_kses_post( $beaver_light_title ); ?></h1>
	<?php if ( $beaver_light_description ) : ?>
		<div class="blt-archive-description"><?php echo wp_kses_post( $beaver_light_description ); ?></div>
	<?php endif; ?>
</header>
