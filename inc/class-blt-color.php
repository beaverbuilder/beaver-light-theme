<?php
/**
 * Color parsing and measurement for design system color inference.
 *
 * Only opaque sRGB colors in hex, rgb() and hsl() parse. Anything else
 * (oklch(), color-mix(), named colors, translucent values) returns null, so
 * inference skips it rather than guessing.
 *
 * @package Beaver_Light_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Color parser and WCAG measurements; a color is array{0: float, 1: float, 2: float} in 0-255.
 */
final class BLT_Color {

	/**
	 * Parse a CSS color value.
	 *
	 * @since 0.1.0
	 * @param string $value CSS color.
	 * @return float[]|null RGB channels, or null when unparseable or translucent.
	 */
	public static function parse( string $value ): ?array {
		$value = strtolower( trim( $value ) );

		if ( preg_match( '/^#([0-9a-f]+)$/', $value, $match ) ) {
			return self::from_hex( $match[1] );
		}

		if ( preg_match( '/^(rgba?|hsla?)\((.*)\)$/s', $value, $match ) ) {
			return self::from_function( $match[1], trim( $match[2] ) );
		}

		return null;
	}

	/**
	 * WCAG relative luminance.
	 *
	 * @since 0.1.0
	 * @param float[] $rgb RGB channels.
	 * @return float 0 (black) to 1 (white).
	 */
	public static function luminance( array $rgb ): float {
		$linear = array();

		foreach ( $rgb as $channel ) {
			$c        = $channel / 255;
			$linear[] = $c <= 0.04045 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		}

		return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
	}

	/**
	 * WCAG contrast ratio between two colors.
	 *
	 * @since 0.1.0
	 * @param float[] $a RGB channels.
	 * @param float[] $b RGB channels.
	 * @return float 1 to 21.
	 */
	public static function contrast( array $a, array $b ): float {
		$la = self::luminance( $a );
		$lb = self::luminance( $b );

		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}

	/**
	 * HSL saturation.
	 *
	 * @since 0.1.0
	 * @param float[] $rgb RGB channels.
	 * @return float 0 to 1.
	 */
	public static function saturation( array $rgb ): float {
		$max   = max( $rgb ) / 255;
		$min   = min( $rgb ) / 255;
		$delta = $max - $min;

		if ( $delta <= 0 ) {
			return 0.0;
		}

		$lightness = ( $max + $min ) / 2;

		return $lightness > 0.5 ? $delta / ( 2 - $max - $min ) : $delta / ( $max + $min );
	}

	/**
	 * Chroma: how far the color is from gray, independent of lightness.
	 *
	 * @since 0.1.0
	 * @param float[] $rgb RGB channels.
	 * @return float 0 to 1.
	 */
	public static function chroma( array $rgb ): float {
		return ( max( $rgb ) - min( $rgb ) ) / 255;
	}

	/**
	 * HSL hue.
	 *
	 * @since 0.1.0
	 * @param float[] $rgb RGB channels.
	 * @return float Degrees, 0 to 360; 0 for grays.
	 */
	public static function hue( array $rgb ): float {
		list( $r, $g, $b ) = $rgb;
		$delta             = max( $rgb ) - min( $rgb );

		if ( $delta <= 0 ) {
			return 0.0;
		}

		if ( max( $rgb ) === $r ) {
			$hue = fmod( ( $g - $b ) / $delta, 6 );
		} elseif ( max( $rgb ) === $g ) {
			$hue = ( $b - $r ) / $delta + 2;
		} else {
			$hue = ( $r - $g ) / $delta + 4;
		}

		return fmod( $hue * 60 + 360, 360 );
	}

	/**
	 * Distance around the hue wheel from a color to a hue.
	 *
	 * @since 0.1.0
	 * @param float[] $rgb RGB channels.
	 * @param float   $hue Degrees.
	 * @return float Degrees, 0 to 180.
	 */
	public static function hue_distance( array $rgb, float $hue ): float {
		$shift = abs( self::hue( $rgb ) - $hue );

		return min( $shift, 360 - $shift );
	}

	/**
	 * Parse 3, 4, 6 or 8 hex digits.
	 *
	 * @since 0.1.0
	 * @param string $hex Hex digits without the hash.
	 * @return float[]|null
	 */
	private static function from_hex( string $hex ): ?array {
		if ( in_array( strlen( $hex ), array( 3, 4 ), true ) ) {
			$hex = preg_replace( '/(.)/', '$1$1', $hex );
		}

		if ( ! in_array( strlen( $hex ), array( 6, 8 ), true ) ) {
			return null;
		}

		$parts = array_map( 'hexdec', str_split( $hex, 2 ) );

		if ( isset( $parts[3] ) && 255 !== $parts[3] ) {
			return null;
		}

		return array_map( 'floatval', array_slice( $parts, 0, 3 ) );
	}

	/**
	 * Parse the arguments of rgb(), rgba(), hsl() or hsla(), comma or space syntax.
	 *
	 * @since 0.1.0
	 * @param string $name Function name.
	 * @param string $args Arguments between the parentheses.
	 * @return float[]|null
	 */
	private static function from_function( string $name, string $args ): ?array {
		$parts = preg_split( '/\s*[,\/]\s*|\s+/', $args );

		if ( count( $parts ) < 3 || count( $parts ) > 4 ) {
			return null;
		}

		// A translucent color's real color depends on what is behind it.
		if ( isset( $parts[3] ) && 1.0 !== self::alpha( $parts[3] ) ) {
			return null;
		}

		return 'h' === $name[0] ? self::from_hsl( $parts ) : self::from_rgb( $parts );
	}

	/**
	 * RGB channels from numbers or percentages.
	 *
	 * @since 0.1.0
	 * @param string[] $parts Channel strings.
	 * @return float[]|null
	 */
	private static function from_rgb( array $parts ): ?array {
		$rgb = array();

		foreach ( array_slice( $parts, 0, 3 ) as $part ) {
			$percent = '%' === substr( $part, -1 );
			$number  = self::number( $percent ? substr( $part, 0, -1 ) : $part );

			if ( null === $number ) {
				return null;
			}

			$rgb[] = max( 0.0, min( 255.0, $percent ? $number * 2.55 : $number ) );
		}

		return $rgb;
	}

	/**
	 * RGB channels from a hue in degrees and saturation and lightness percentages.
	 *
	 * @since 0.1.0
	 * @param string[] $parts Hue, saturation and lightness strings.
	 * @return float[]|null
	 */
	private static function from_hsl( array $parts ): ?array {
		$hue        = self::number( preg_replace( '/deg$/', '', $parts[0] ) );
		$saturation = self::number( rtrim( $parts[1], '%' ) );
		$lightness  = self::number( rtrim( $parts[2], '%' ) );

		if ( null === $hue || null === $saturation || null === $lightness ) {
			return null;
		}

		$s      = max( 0.0, min( 1.0, $saturation / 100 ) );
		$l      = max( 0.0, min( 1.0, $lightness / 100 ) );
		$chroma = ( 1 - abs( 2 * $l - 1 ) ) * $s;
		$rgb    = array();

		foreach ( array( 0, 8, 4 ) as $offset ) {
			$k     = fmod( $offset + fmod( $hue, 360 ) / 30 + 12, 12 );
			$rgb[] = 255 * ( $l - $chroma / 2 * max( -1, min( $k - 3, 9 - $k, 1 ) ) );
		}

		return $rgb;
	}

	/**
	 * An alpha value as a fraction.
	 *
	 * @since 0.1.0
	 * @param string $part Alpha as a number or percentage.
	 * @return float|null
	 */
	private static function alpha( string $part ): ?float {
		$percent = '%' === substr( $part, -1 );
		$number  = self::number( $percent ? substr( $part, 0, -1 ) : $part );

		return ( null !== $number && $percent ) ? $number / 100 : $number;
	}

	/**
	 * A plain decimal number, or null.
	 *
	 * @since 0.1.0
	 * @param string $part Number string.
	 * @return float|null
	 */
	private static function number( string $part ): ?float {
		return preg_match( '/^[+-]?(\d+\.?\d*|\.\d+)$/', $part ) ? (float) $part : null;
	}
}
