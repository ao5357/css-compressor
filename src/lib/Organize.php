<?php
/**
 * CSS Compressor [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

Class CSSCompression_Organize
{
	/**
	 * Organize Patterns
	 *
	 * @class Control: Compression Controller
	 * @param (array) options: Reference to options
	 * @param (regex) rsemicolon: Checks for semicolon without an escape '\' character before it
	 */
	private $Control;
	private $options = array();
	private $rsemicolon = "/(?<!\\\);/";

	/**
	 * Stash a reference to the controller on each instantiation
	 *
	 * @param (class) control: CSSCompression Controller
	 */
	public function __construct( CSSCompression_Control $control ) {
		$this->Control = $control;
		$this->options = &$control->Option->options;
	}

	/**
	 * Look to see if we can combine selectors to reduce the number
	 * of definitions.
	 *
	 * @param (array) selectors: Array of selectors, map directly to details
	 * @param (array) details: Array of details, map directly to selectors
	 */
	public function organize( $selectors = array(), $details = array() ) {
		// Combining defns based on similar selectors
		list ( $selectors, $details ) = $this->reduceSelectors( $selectors, $details );

		// Combining defns based on similar details
		list ( $selectors, $details ) = $this->reduceDetails( $selectors, $details );

		// Return in package form
		return array( $selectors, $details );
	}

	/**
	 * Combines multiply defined selectors by merging the definitions,
	 * latter definitions overide definitions at top of file
	 *
	 * @param (array) selectors: Array of selectors broken down by setup
	 * @param (array) details: Array of details broken down by setup
	 */ 
	private function reduceSelectors( $selectors, $details ) {
		$max = array_pop( array_keys( $selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( ! isset( $selectors[ $i ] ) ) {
				continue;
			}

			for ( $k = $i + 1; $k < $max; $k++ ) {
				if ( ! isset( $selectors[ $k ] ) ) {
					continue;
				}

				if ( $selectors[ $i ] == $selectors[ $k ] ) {
					if ( ! isset( $details[ $i ] ) ) {
						$details[ $i ] = '';
					}
					if ( ! isset( $details[ $k ] ) ) {
						$details[ $k ] = '';
					}
					$details[ $i ] .= $details[ $k ];
					unset( $selectors[ $k ], $details[ $k ] );
				}
			}
		}

		return array( $selectors, $details );
	}

	/**
	 * Combines multiply defined details by merging the selectors
	 * in comma seperated format
	 *
	 * @param (array) selectors: Array of selectors broken down by setup
	 * @param (array) details: Array of details broken down by setup
	 */ 
	private function reduceDetails( $selectors, $details ) {
		$max = array_pop( array_keys( $selectors ) ) + 1;
		for ( $i = 0; $i < $max; $i++ ) {
			if ( ! isset( $selectors[ $i ] ) ) {
				continue;
			}

			$arr = preg_split( $this->rsemicolon, isset( $details[ $i ] ) ? $details[ $i ] : '' );
			for ( $k = $i + 1; $k < $max; $k++ ) {
				if ( ! isset( $selectors[ $k ] ) ) {
					continue;
				}

				$match = preg_split( $this->rsemicolon, isset( $details[ $k ] ) ? $details[ $k ] : '' );
				$x = array_diff( $arr, $match );
				$y = array_diff( $match, $arr );

				if ( count( $x ) < 1 && count( $y ) < 1 ) {
					$selectors[ $i ] .= ',' . $selectors[ $k ];
					unset( $details[ $k ], $selectors[ $k ] );
				}
			}
		}

		return array( $selectors, $details );
	}
};

?>
