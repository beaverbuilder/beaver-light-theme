<?php
/**
 * Selection over a design system's measurable colors, for BLT_Color_Rules.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Measurable colors by token name, with the picks the rules make from them.
 */
final class BLT_Color_Palette {

	/**
	 * Measurable colors, name => RGB channels, in token map order.
	 *
	 * @var array
	 */
	private $colors;

	/**
	 * Hold the measurable colors.
	 *
	 * @since 0.1.0
	 * @param array $colors Name => RGB channels.
	 */
	public function __construct( array $colors ) {
		$this->colors = $colors;
	}

	/**
	 * A token's RGB channels.
	 *
	 * @since 0.1.0
	 * @param string $name Token name.
	 * @return array|null Null when the token cannot be measured.
	 */
	public function rgb( string $name ): ?array {
		return $this->colors[ $name ] ?? null;
	}

	/**
	 * The highest-scoring color that passes the filter; ties go to the earlier token.
	 *
	 * @since 0.1.0
	 * @param callable|null $filter Receives RGB channels and name; null accepts all.
	 * @param callable      $score  Receives RGB channels.
	 * @return string|null Token name.
	 */
	public function best( ?callable $filter, callable $score ): ?string {
		$best  = null;
		$floor = -INF;

		foreach ( $this->colors as $name => $rgb ) {
			$value = (float) call_user_func( $score, $rgb );

			if ( $value > $floor && ( null === $filter || call_user_func( $filter, $rgb, (string) $name ) ) ) {
				$best  = (string) $name;
				$floor = $value;
			}
		}

		return $best;
	}

	/**
	 * Contrast between two tokens, 0 when either cannot be measured.
	 *
	 * @since 0.1.0
	 * @param string $a Token name.
	 * @param string $b Token name.
	 * @return float
	 */
	public function ratio( string $a, string $b ): float {
		$known = isset( $this->colors[ $a ], $this->colors[ $b ] );

		return $known ? BLT_Color::contrast( $this->colors[ $a ], $this->colors[ $b ] ) : 0.0;
	}
}
