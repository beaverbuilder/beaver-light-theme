<?php
/**
 * Native footer: the copyright line and the footer menu. No widgets.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="blt-footer">
	<div class="blt-container blt-footer__inner">
		<p class="blt-copyright"><?php echo esc_html( BLT_Chrome::copyright() ); ?></p>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="blt-footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'beaver-light-theme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'blt-footer-menu',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</footer>
