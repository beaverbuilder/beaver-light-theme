<?php
/**
 * Native header: branding, then the header menu with a toggle below the breakpoint.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="blt-header">
	<div class="blt-container blt-header__inner">
		<div class="blt-branding">
			<?php beaver_light_site_branding(); ?>
		</div>

		<?php if ( has_nav_menu( 'header' ) ) : ?>
			<button class="blt-nav-toggle" aria-expanded="false" aria-controls="blt-nav">
				<span class="blt-nav-toggle__bars" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'beaver-light' ); ?></span>
			</button>
			<nav id="blt-nav" class="blt-nav" aria-label="<?php esc_attr_e( 'Primary', 'beaver-light' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'header',
						'container'      => false,
						'menu_class'     => 'blt-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</header>
