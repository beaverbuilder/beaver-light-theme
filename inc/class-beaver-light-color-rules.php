<?php
/**
 * The value-based rules that pick a color token for each unmatched role.
 *
 * A wrong guess is worse than no guess, so a role commits only when its
 * contrast checks pass, and a known muted name, which some systems give to a
 * background tone, must pass the text check too; otherwise it falls through to
 * Global Styles and then the theme default.
 *
 * @package Beaver_Light
 */

defined( 'ABSPATH' ) || exit;

/**
 * One inference pass over a design system's colors.
 */
final class Beaver_Light_Color_Rules {

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

	/** HSL saturation runs high near black, so a brand color also needs this much chroma. */
	const PRIMARY_MIN_CHROMA = 0.15;

	/** Muted text must be visibly lighter than text, at most this share of its contrast. */
	const MUTED_MAX_SHARE = 0.75;

	/** A hover shade keeps the primary's hue within this many degrees. */
	const HOVER_MAX_HUE_SHIFT = 20.0;

	/** A gray's hue is meaningless, so it cannot be or match the primary's hue. */
	const HOVER_MIN_SATURATION = 0.2;

	/** What infer() returns when it can measure nothing. */
	const NONE = array(
		'roles'    => array(),
		'rejected' => array(),
	);

	/**
	 * The measurable colors the roles are picked from.
	 *
	 * @var Beaver_Light_Color_Palette
	 */
	private $palette;

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
		$this->palette = new Beaver_Light_Color_Palette( $colors );
		$this->roles   = $seeds;
	}

	/**
	 * Infer every role the seeds left open, after rejecting the known muted names too faint on the surface.
	 *
	 * @since 0.1.0
	 * @param string[] $muted_names Known muted names present, in listed order.
	 * @return array{roles: array, rejected: array} Role => token name for inferred roles; role => rejected names.
	 */
	public function infer( array $muted_names ): array {
		$seeds = $this->roles;

		// Every other check measures against surface and text, so without both nothing is inferred or rejected.
		if ( ! $this->infer_surface_and_text() ) {
			return self::NONE;
		}

		$faint = $this->palette->faint( $muted_names, $this->roles['surface'], self::TEXT_CONTRAST );
		$seeds = $this->seed_muted( $seeds, array_values( array_diff( $muted_names, $faint ) ) );
		$this->fill( 'heading', $this->roles['text'] );
		$this->fill( 'muted', $this->pick_muted() );
		$this->fill( 'line', $this->pick_line() );
		$this->infer_primary();
		$this->infer_primary_hover();

		if ( isset( $this->roles['primary'] ) && $this->palette->ratio( $this->roles['primary'], $this->roles['surface'] ) >= self::TEXT_CONTRAST ) {
			$this->fill( 'link', $this->roles['primary'] );
		}

		if ( isset( $this->roles['link'], $this->roles['primary-hover'] ) && $this->palette->ratio( $this->roles['primary-hover'], $this->roles['surface'] ) >= self::TEXT_CONTRAST ) {
			$this->fill( 'link-hover', $this->roles['primary-hover'] );
		}

		return array(
			'roles'    => array_diff_key( $this->roles, $seeds ),
			'rejected' => array() === $faint ? array() : array( 'muted' => $faint ),
		);
	}

	/**
	 * Surface is the lightest color and text the darkest, committed only together and at text contrast.
	 *
	 * @since 0.1.0
	 * @return bool Whether both roles are set.
	 */
	private function infer_surface_and_text(): bool {
		$surface = $this->roles['surface'] ?? $this->palette->best( null, array( 'Beaver_Light_Color', 'luminance' ) );
		$text    = $this->roles['text'] ?? $this->palette->best(
			null,
			static function ( array $rgb ): float {
				return -Beaver_Light_Color::luminance( $rgb );
			}
		);

		if ( null === $surface || null === $text || $this->palette->ratio( $text, $surface ) < self::TEXT_CONTRAST ) {
			return false;
		}

		$this->roles['surface'] = $surface;
		$this->roles['text']    = $text;

		return true;
	}

	/**
	 * Seed muted with the first legible known name, or leave it open for inference.
	 *
	 * @since 0.1.0
	 * @param array    $seeds   Role => token name matched by known names.
	 * @param string[] $legible Known muted names that passed, in listed order.
	 * @return array
	 */
	private function seed_muted( array $seeds, array $legible ): array {
		unset( $seeds['muted'], $this->roles['muted'] );

		if ( isset( $legible[0] ) ) {
			$seeds['muted']       = $legible[0];
			$this->roles['muted'] = $legible[0];
		}

		return $seeds;
	}

	/**
	 * The neutral with the lowest contrast on the surface that still reads as text, and is visibly lighter than text.
	 *
	 * @since 0.1.0
	 * @return string|null
	 */
	private function pick_muted(): ?string {
		$surface = $this->palette->rgb( $this->roles['surface'] );
		$text    = $this->roles['text'];
		$ceiling = $this->palette->ratio( $text, $this->roles['surface'] ) * self::MUTED_MAX_SHARE;

		return $this->palette->best(
			static function ( array $rgb, string $name ) use ( $surface, $text, $ceiling ): bool {
				$contrast = Beaver_Light_Color::contrast( $rgb, $surface );
				$neutral  = Beaver_Light_Color::saturation( $rgb ) <= self::NEUTRAL_MAX_SATURATION;
				return $name !== $text && $neutral && $contrast >= self::TEXT_CONTRAST && $contrast <= $ceiling;
			},
			static function ( array $rgb ) use ( $surface ): float {
				return -Beaver_Light_Color::contrast( $rgb, $surface );
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
		$surface = $this->palette->rgb( $this->roles['surface'] );

		return $this->palette->best(
			static function ( array $rgb ) use ( $surface ): bool {
				$contrast = Beaver_Light_Color::contrast( $rgb, $surface );
				$in_range = $contrast >= self::LINE_MIN_CONTRAST && $contrast <= self::LINE_MAX_CONTRAST;
				return $in_range && Beaver_Light_Color::saturation( $rgb ) <= self::NEUTRAL_MAX_SATURATION;
			},
			static function ( array $rgb ) use ( $surface ): float {
				return -Beaver_Light_Color::contrast( $rgb, $surface );
			}
		);
	}

	/**
	 * Primary is the most colorful color that works as a fill and has a readable on-primary; chroma ranks, since HSL saturation is flat across a shade series.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function infer_primary(): void {
		$surface = $this->palette->rgb( $this->roles['surface'] );
		$primary = $this->roles['primary'] ?? $this->palette->best(
			function ( array $rgb, string $name ) use ( $surface ): bool {
				$colorful = Beaver_Light_Color::saturation( $rgb ) >= self::PRIMARY_MIN_SATURATION && Beaver_Light_Color::chroma( $rgb ) >= self::PRIMARY_MIN_CHROMA;
				return $colorful && Beaver_Light_Color::contrast( $rgb, $surface ) >= self::UI_CONTRAST && null !== $this->on_primary_for( $name );
			},
			array( 'Beaver_Light_Color', 'chroma' )
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
		$fill = $this->palette->rgb( $primary );
		$on   = $this->roles['on-primary'] ?? ( null === $fill ? null : $this->palette->best(
			null,
			static function ( array $rgb ) use ( $fill ): float {
				return Beaver_Light_Color::contrast( $rgb, $fill );
			}
		) );

		return ( null !== $on && $this->palette->ratio( $on, $primary ) >= self::TEXT_CONTRAST ) ? $on : null;
	}

	/**
	 * The primary's hue at the nearest lower luminance, still readable under on-primary; none for a gray primary.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function infer_primary_hover(): void {
		$fill = isset( $this->roles['primary'], $this->roles['on-primary'] ) ? $this->palette->rgb( $this->roles['primary'] ) : null;
		$on   = null === $fill ? null : $this->palette->rgb( $this->roles['on-primary'] );

		// A gray primary has no hue for a hover shade to keep.
		if ( null === $on || Beaver_Light_Color::saturation( $fill ) < self::HOVER_MIN_SATURATION ) {
			return;
		}

		$hue       = Beaver_Light_Color::hue( $fill );
		$luminance = Beaver_Light_Color::luminance( $fill );

		$this->fill(
			'primary-hover',
			$this->palette->best(
				static function ( array $rgb ) use ( $hue, $luminance, $on ): bool {
					$same = Beaver_Light_Color::hue_distance( $rgb, $hue ) <= self::HOVER_MAX_HUE_SHIFT && Beaver_Light_Color::saturation( $rgb ) >= self::HOVER_MIN_SATURATION;
					return $same && Beaver_Light_Color::luminance( $rgb ) < $luminance && Beaver_Light_Color::contrast( $rgb, $on ) >= self::TEXT_CONTRAST;
				},
				array( 'Beaver_Light_Color', 'luminance' )
			)
		);
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
