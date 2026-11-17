<?php
/**
 * A display-only Customizer control: a sentence and links, no input.
 *
 * Loaded from Beaver_Light_Customizer::register(), because its parent class
 * exists only while the Customizer loads.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * Signpost control for Beaver_Light_Customizer.
 */
final class Beaver_Light_Customize_Pointer_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'beaver_light_pointer';

	/**
	 * Links to show, each with a label, a URL and where it lives (see Beaver_Light_Customizer::links()).
	 *
	 * @var array[]
	 */
	public $links = array();

	/**
	 * Print the sentence and the links.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	protected function render_content() {
		?>
		<p><?php esc_html_e( 'Beaver Light has no color or font settings of its own. Change the look of the site here:', 'beaver-light' ); ?></p>
		<ul>
			<?php foreach ( $this->links as $link ) : ?>
				<li>
					<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link['label'] ); ?></a>
					<span class="description customize-control-description"><?php echo esc_html( $link['where'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
