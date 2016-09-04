<?php
/**
 * This file defines the Autoloader class.
 *
 * @package PluginPackage
 */

namespace PluginTemplateNS;

/**
 * Class Autoloader
 */
class Autoloader {

	/**
	 * Autoloader include directory.
	 *
	 * @var string
	 */
	private $include_dir;

	/**
	 * Autoloader caching.
	 *
	 * @var array
	 */
	private $found_matches = array();

	/**
	 * Register the autoloader method.
	 *
	 * @param string $include_dir Autoloader include directory.
	 * @param bool   $prepend     Whether the function should be prepended to the stack.
	 */
	public function register( $include_dir, $prepend = false ) {
		$this->include_dir = $include_dir;
		spl_autoload_register( array( $this, 'autoload' ), true, $prepend );
	}

	/**
	 * Autoloader.
	 *
	 * @param string $class Unknown class to attempt to load.
	 *
	 * @return bool|void
	 */
	public function autoload( $class ) {

		// If its not a PluginTemplateNS class, exit now.
		if ( ! preg_match( '/^PluginTemplateNS/', $class ) ) {
			return false;
		}

		if ( ! isset( $this->found_matches[ $class ] ) ) {

			if ( ! preg_match( '/^(?P<namespace>.+)\\\\(?P<class>[^\\\\]+)$/', $class, $matches ) ) {
				$matches = false;
			}

			$this->found_matches[ $class ] = $matches;
		} else {
			$matches = $this->found_matches[ $class ];
		}

		$class_parts = explode( '\\', $matches['namespace'] );
		if ( ! empty( $class_parts ) && 'PluginTemplateNS' === $class_parts[0] ) {
			array_shift( $class_parts );
		} else {
			return false;
		}

		foreach ( $class_parts as $key => $item ) {
			$class_parts[ $key ] = strtolower( implode( '-', array_filter( preg_split( '/(?=[A-Z])(?<![A-Z])/', $item ) ) ) );
		}

		$class_string = strtolower( implode( '-', array_filter( preg_split( '/(?=[A-Z])(?<![A-Z])/', $matches['class'] ) ) ) );
		$class_path   = ! empty( $class_parts ) ? implode( DIRECTORY_SEPARATOR, array_filter( $class_parts ) ) . DIRECTORY_SEPARATOR : '';
		$basedir      = $this->include_dir . $class_path;

		if ( ! empty( $class_string ) ) {

			// One last chance to override the filename.
			$filename = apply_filters( 'plugin_hook_prefix_class_file_override', $basedir . 'class-' . $class_string . '.php', $class );

			// Include it if it exists and we have access.
			if ( is_readable( $filename ) ) {
				include_once $filename;

				return true;
			}
		}

		return false;
	}
}
