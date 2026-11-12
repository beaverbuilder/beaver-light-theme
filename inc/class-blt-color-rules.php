<?php
/**
 * The value-based rules that pick a color token for each unmatched role.
 *
 * A wrong guess is worse than no guess, so a role commits only when its
 * contrast checks pass; otherwise it is left out and falls through to Global
 * Styles and then the theme default.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * One inference pass over a design system's colors.
 */
final class BLT_Color_Rules {

	/** WCAG AA for body text. */
	const TEXT_CONTRAST = 4.5;

	/** WCAG AA for UI components, which a button fill is. */
	const UI_CONTRAST = 3.0;

	/** Muted text and lines should read as gray, not as a brand color. */
	const NEUTRAL_MAX_SATURATION = 0.3;

	/** A line has to be visible on the surface without competing with text. */
	const LINE_MIN_CONTRAST = 1.25;

	/** Above this a color reads as text or a fill, not a line. */
	const LINE_MAX_CONTRAST = 3.0;

	/** Below this a color is a tinted neutral, not a brand color. */
	const PRIMARY_MIN_SATURATION = 0.35;

	/** A hover shade keeps the primary's hue within this many degrees. */
	const HOVER_MAX_HUE_SHIFT = 20.0;

	/** A gray's hue is meaningless, so it cannot match the primary's. */
	const HOVER_MIN_SATURATION = 0.2;

	/**
	 * Measurable colors, name => RGB channels, in token map order.
	 *
	 * @var array
	 */
	private $colors;

	/**
	 * Role => token name, seeded from known names and filled in by the rules.
	 *
	 * @var array
	 */
	private $roles;

	/**
	 * Start from the measurable colors and the roles known names already matched.
	 *
	 * @since 0.1.0
	 * @param array $colors Name => RGB channels.
	 * @param array $seeds  Role => token name matched by known names.
	 */
	public function __construct( array $colors, array $seeds ) {
		$this->colors = $colors;
		$this->roles  = $seeds;
	}

	/**
	 * Infer every role the seeds left open.
	 *
	 * @since 0.1.0
	 * @return array Role => token name, for inferred roles only.
	 */
	public function infer(): array {
		$seeds = $this->roles;

		// Every other check measures against surface and text, so without both nothing is inferred.
		if ( ! $this->infer_surface_and_text() ) {
			return array();
		}

		$this->fill( 'heading', $this->roles['text'] );
		$this->fill( 'muted', $this->pick_muted() );
		$this->fill( 'line', $this->pick_line() );
		$this->infer_primary();
		$this->infer_primary_hover();

		if ( isset( $this->roles['primary'] ) && $this->ratio( $this->roles['primary'], $this->roles['surface'] ) >= self::TEXT_CONTRAST ) {
			$this->fill( 'link', $this->roles['primary'] );
		}

		if ( isset( $this->roles['primary-hover'] ) && $this->ratio( $this->roles['primary-hover'], $this->roles['surface'] ) >= self::TEXT_CONTRAST ) {
			$this->fill( 'link-hover', $this->roles['primary-hover'] );
		}

		return array_diff_key( $this->roles, $seeds );
	}

	/**
	 * Surface is the lightest color and text the darkest, committed only together and at text contrast.
	 *
	 * @since 0.1.0
	 * @return bool Whether both roles are set.
	 */
	private function infer_surface_and_text(): bool {
		$surface = $this->roles['surface'] ?? $this->best( null, array( 'BLT_Color', 'luminance' ) );
		$text    = $this->roles['text'] ?? $this->best(
			null,
			static function ( array $rgb ): float {
				return -BLT_Color::luminance( $rgb );
			}
		);

		if ( null === $surface || null === $text || $this->ratio( $text, $surface ) < self::TEXT_CONTRAST ) {
			return false;
		}

		$this->roles['surface'] = $surface;
		$this->roles['text']    = $text;

		return true;
	}

	/**
	 * The neutral with the lowest contrast on the surface that still reads as text, and is lighter than text.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	private function pick_muted(): ?string {
		$surface = $this->colors[ $this->roles['surface'] ];
		$text    = $this->roles['text'];
		$ceiling = $this->ratio( $text, $this->roles['surface'] );

		return $this->best(
			static function ( array $rgb, string $name ) use ( $surface, $text, $ceiling ): bool {
				$contrast = BLT_Color::contrast( $rgb, $surface );
				$neutral  = BLT_Color::saturation( $rgb ) <= self::NEUTRAL_MAX_SATURATION;
				return $name !== $text && $neutral && $contrast >= self::TEXT_CONTRAST && $contrast < $ceiling;
			},
			static function ( array $rgb ) use ( $surface ): float {
				return -BLT_Color::contrast( $rgb, $surface );
			}
		);
	}

	/**
	 * The subtlest neutral that is still visible as a line on the surface.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	private function pick_line(): ?string {
		$surface = $this->colors[ $this->roles['surface'] ];

		return $this->best(
			static function ( array $rgb ) use ( $surface ): bool {
				$contrast = BLT_Color::contrast( $rgb, $surface );
				$in_range = $contrast >= self::LINE_MIN_CONTRAST && $contrast <= self::LINE_MAX_CONTRAST;
				return $in_range && BLT_Color::saturation( $rgb ) <= self::NEUTRAL_MAX_SATURATION;
			},
			static function ( array $rgb ) use ( $surface ): float {
				return -BLT_Color::contrast( $rgb, $surface );
			}
		);
	}

	/**
	 * Primary is the most colorful saturated color that works as a fill and has a readable on-primary.
	 *
	 * Ranked by chroma, because HSL saturation stays flat across a shade series and would pick the darkest shade.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function infer_primary(): void {
		$surface = $this->colors[ $this->roles['surface'] ];
		$primary = $this->roles['primary'] ?? $this->best(
			function ( array $rgb, string $name ) use ( $surface ): bool {
				$fill = BLT_Color::saturation( $rgb ) >= self::PRIMARY_MIN_SATURATION && BLT_Color::contrast( $rgb, $surface ) >= self::UI_CONTRAST;
				return $fill && null !== $this->on_primary_for( $name );
			},
			array( 'BLT_Color', 'chroma' )
		);
		$on      = null === $primary ? null : $this->on_primary_for( $primary );

		if ( null !== $on ) {
			$this->fill( 'primary', $primary );
			$this->fill( 'on-primary', $on );
		}
	}

	/**
	 * The on-primary for a fill: the matched one, else the highest-contrast color, if it reaches text contrast.
	 *
	 * @since 0.1.0
	 * @param string $primary Token name of the fill.
	 * @return string|null
	 */
	private function on_primary_for( string $primary ): ?string {
		$fill = $this->colors[ $primary ] ?? null;
		$on   = $this->roles['on-primary'] ?? ( null === $fill ? null : $this->best(
			null,
			static function ( array $rgb ) use ( $fill ): float {
				return BLT_Color::contrast( $rgb, $fill );
			}
		) );

		return ( null !== $on && $this->ratio( $on, $primary ) >= self::TEXT_CONTRAST ) ? $on : null;
	}

	/**
	 * The primary's hue at the nearest lower luminance, still readable under on-primary.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function infer_primary_hover(): void {
		$fill = isset( $this->roles['primary'], $this->roles['on-primary'] ) ? ( $this->colors[ $this->roles['primary'] ] ?? null ) : null;
		$on   = null === $fill ? null : ( $this->colors[ $this->roles['on-primary'] ] ?? null );

		if ( null === $on ) {
			return;
		}

		$hue       = BLT_Color::hue( $fill );
		$luminance = BLT_Color::luminance( $fill );

		$this->fill(
			'primary-hover',
			$this->best(
				static function ( array $rgb ) use ( $hue, $luminance, $on ): bool {
					$shift = abs( BLT_Color::hue( $rgb ) - $hue );
					$same  = min( $shift, 360 - $shift ) <= self::HOVER_MAX_HUE_SHIFT && BLT_Color::saturation( $rgb ) >= self::HOVER_MIN_SATURATION;
					return $same && BLT_Color::luminance( $rgb ) < $luminance && BLT_Color::contrast( $rgb, $on ) >= self::TEXT_CONTRAST;
				},
				array( 'BLT_Color', 'luminance' )
			)
		);
	}

	/**
	 * The highest-scoring color that passes the filter; ties go to the earlier token.
	 *
	 * @since 0.1.0
	 * @param callable|null $filter Receives RGB channels and name; null accepts all.
	 * @param callable      $score  Receives RGB channels.
	 * @return string|null Token name.
	 */
	private function best( ?callable $filter, callable $score ): ?string {
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
	private function ratio( string $a, string $b ): float {
		$known = isset( $this->colors[ $a ], $this->colors[ $b ] );

		return $known ? BLT_Color::contrast( $this->colors[ $a ], $this->colors[ $b ] ) : 0.0;
	}

	/**
	 * Set a role unless known names already matched it.
	 *
	 * @since 0.1.0
	 * @param string      $role Role.
	 * @param string|null $name Token name, or null to leave the role open.
	 * @return void
	 */
	private function fill( string $role, ?string $name ): void {
		if ( null !== $name && ! isset( $this->roles[ $role ] ) ) {
			$this->roles[ $role ] = $name;
		}
	}
}
