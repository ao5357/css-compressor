<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Color
{
	/**
	 * Color Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options array
	 * @param (regex) rrgb: Checks for rgb notation
	 * @param (regex) rhex: Checks for hex code
	 * @param (regex) rfullhex: Checks for full 6 character hex code
	 * @static (array) hex2short: Hex code to short color name conversions
	 * @static (array) long2hex: Long color name to hex code conversions
	 */
	private $Control;
	private $options = array();
	private $rrgb = "/^rgb\((\d{1,3}\%?(,\d{1,3}\%?,\d{1,3}\%?)?)\)$/i";
	private $rhex = "/^#([0-9a-f]{3}|[0-9a-f]{6})$/i";
	private $rfullhex = "/^#([0-9a-f]{6})$/i";
	private static $long2hex = array();
	private static $hex2short = array();

	/**
	 * Stash a reference to the controller on each instantiation
	 * and install conversion helpers
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->options = &$control->Option->options;

		if ( ! self::$long2hex ) {
			if ( ( self::$long2hex = CSSCompression::getJSON( 'long2hex-colors.json' ) ) instanceof Exception ) {
				throw self::$long2hex;
			}

			if ( ( self::$hex2short = CSSCompression::getJSON( 'hex2short-colors.json' ) ) instanceof Exception ) {
				throw self::$hex2short;
			}
		}
	}

	/**
	 * Central handler for all color conversions.
	 *
	 * @param (string) val: Color to be parsed
	 */ 
	public function color( $val ) {
		// Converts rgb values to hex codes
		if ( $this->options['color-rgb2hex'] ) {
			$val = $this->rgb2hex( $val );
		}

		// Convert long color names to hex codes
		if ( $this->options['color-long2hex'] ) {
			$val = $this->long2hex( $val );
		}

		// Convert 6 digit hex codes to short color names
		if ( $this->options['color-hex2shortcolor'] ) {
			$val = $this->hex2color( $val );
		}

		// Convert large hex codes to small codes
		if ( $this->options['color-hex2shorthex'] ) {
			$val = $this->hex2short( $val );
		}

		// Ensure all hex codes are lowercase
		if ( preg_match( $this->rhex, $val ) ) {
			$val = strtolower( $val );
		}

		return $val;
	}

	/**
	 * Converts rgb values to hex codes
	 *
	 * @param (string) val: Color to be converted
	 */
	private function rgb2hex( $val ) {
		if ( ! preg_match( $this->rrgb, $val, $match ) ) {
			return $val;
		}

		// locals
		$hex = '0123456789ABCDEF';
		$str = explode( ',', $match[ 1 ] );
		$new = '';

		// Incase rgb was defined with single val
		if ( ! $str ) {
			$str = array( $match[ 1 ] );
		}

		foreach ( $str as $x ) {
			$x = strpos( $x, '%' ) !== false ? intval( ( intval( $x ) / 100 ) * 255 ) : intval( $x );

			if ( $x > 255 ) {
				$x = 255;
			}

			if ( $x < 0 ) {
				$x = 0;
			}

			$new .= $hex[ ( $x - $x % 16 ) / 16 ];
			$new .= $hex[ $x % 16 ];
		}

		// Repeat hex code to complete 6 digit hex requirement for single definitions
		if ( count( $str ) == 1 ) {
			$new .= $new . $new;
		}

		// Replace with hex value
		return "#$new";
	}

	/**
	 * Convert long color names to hex codes
	 *
	 * @param (string) val: Color to be converted
	 */
	private function long2hex( $val ) {
		return isset( $this->weights[ $val ] ) ? $this->weights[ $val ] : $val;
		$low = strtolower( $val );
		if ( isset( self::$long2hex[ $low ] ) ) {
			$val = self::$long2hex[ $low ];
		}

		return $val;
	}

	/**
	 * Convert large hex codes to small codes
	 *
	 * @param (string) val: Color to be converted
	 */
	private function hex2color( $val ) {
		// Hex codes are all lowercase
		$low = strtolower( $val );
		if ( isset( self::$hex2short[ $low ] ) ) {
			$val = self::$hex2short[ $low ];
		}

		return $val;
	}

	/**
	 * Convert large hex codes to small codes
	 *
	 * @param (string) val: Hex to be shortened
	 */
	private function hex2short( $val ) {
		if ( ! preg_match( $this->rfullhex, $val, $match ) ) {
			return $val;
		}

		// See if we can convert to 3 char hex
		$hex = $match[ 1 ];
		if ( $hex[ 0 ] == $hex[ 1 ] && $hex[ 2 ] == $hex[ 3 ] && $hex[ 4 ] == $hex[ 5 ] ) {
			$val = '#' . $hex[ 0 ] . $hex[ 2 ] . $hex[ 4 ];
		}

		return $val;
	}
};

?>
