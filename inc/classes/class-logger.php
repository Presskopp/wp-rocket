<?php
namespace WP_Rocket;

use Monolog\Logger as Monologger;
use Monolog\Registry;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class used to log events.
 *
 * @since  3.1.4
 * @author Grégory Viguier
 */
class Logger {

	/**
	 * Logger name.
	 *
	 * @var    string
	 * @since  3.1.4
	 * @author Grégory Viguier
	 */
	const LOGGER_NAME = 'wp_rocket';

	/**
	 * Name of the logs file.
	 *
	 * @var    string
	 * @since  3.1.4
	 * @author Grégory Viguier
	 */
	const LOG_FILE_NAME = 'wp-rocket-debug.log';


	/** ----------------------------------------------------------------------------------------- */
	/** LOG ===================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Adds a log record at the DEBUG level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function debug( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->debug( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the INFO level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function info( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->info( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the NOTICE level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function notice( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->notice( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the WARNING level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function warning( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->warning( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the ERROR level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function error( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->error( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the CRITICAL level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function critical( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->critical( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the ALERT level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function alert( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->alert( $message, $context ) : null;
	}

	/**
	 * Adds a log record at the EMERGENCY level.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $message The log message.
	 * @param  array  $context The log context.
	 * @return bool|null       Whether the record has been processed.
	 */
	public static function emergency( $message, array $context = array() ) {
		return static::debug_enabled() ? static::get_logger()->emergency( $message, $context ) : null;
	}

	/**
	 * Get the logger instance.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return Logger A Logger instance.
	 */
	public static function get_logger() {
		$logger_name = static::LOGGER_NAME;
		$log_level   = Monologger::DEBUG;

		if ( Registry::hasLogger( $logger_name ) ) {
			return Registry::$logger_name();
		}

		/**
		 * File handler.
		 * HTML formatter is used.
		 *
		 * Note, if Inline formatter is used:
		 *     - Keep default formats, allow line breaks in messages, hide empty contexts and extras.
		 *     - `$formatter = new LineFormatter( null, null, true, true );`.
		 */
		$handler   = new StreamHandler( static::get_log_file_path(), $log_level );
		$formatter = new HtmlFormatter();

		$handler->setFormatter( $formatter );

		/**
		 * Thanks to the processors, add data to each log:
		 * - `debug_backtrace()` (exclude this class).
		 */
		$trace_processor = new Processor\IntrospectionProcessor( $log_level, [ get_called_class() ] );

		// Create the logger.
		$logger = new Monologger( $logger_name, [ $handler ], [ $trace_processor ] );

		// Store the logger.
		Registry::addLogger( $logger );

		return $logger;
	}


	/** ----------------------------------------------------------------------------------------- */
	/** LOG FILE ================================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the path to the log file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public static function get_log_file_path() {
		if ( defined( 'WP_ROCKET_DEBUG_LOG_FILE' ) && WP_ROCKET_DEBUG_LOG_FILE && is_string( WP_ROCKET_DEBUG_LOG_FILE ) ) {
			// Make sure the file uses a ".log" extension.
			return preg_replace( '/\.[^.]*$/', '', WP_ROCKET_DEBUG_LOG_FILE ) . '.log';
		}

		return WP_CONTENT_DIR . '/wp-rocket-config/' . static::LOG_FILE_NAME;
	}

	/**
	 * Get the log file contents.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string|object The file contents on success. A WP_Error object on failure.
	 */
	public static function get_log_file_contents() {
		$filesystem = \rocket_direct_filesystem();
		$file_path  = static::get_log_file_path();

		if ( ! $filesystem->exists( $file_path ) ) {
			return new \WP_Error( 'no_file', __( 'The log file does not exist.', 'rocket' ) );
		}

		$contents = $filesystem->get_contents( $file_path );

		if ( false === $contents ) {
			return new \WP_Error( 'file_not_read', __( 'The log file could not be read.', 'rocket' ) );
		}

		return $contents;
	}

	/**
	 * Get the log file size and number of entries.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array|object An array of statistics on success. A WP_Error object on failure.
	 */
	public static function get_log_file_stats() {
		$formatter = static::get_stream_formatter();

		if ( ! $formatter ) {
			return new \WP_Error( 'no_stream_formatter', __( 'The logs are not saved into a file.', 'rocket' ) );
		}

		$filesystem = \rocket_direct_filesystem();
		$file_path  = static::get_log_file_path();

		if ( ! $filesystem->exists( $file_path ) ) {
			return new \WP_Error( 'no_file', __( 'The log file does not exist.', 'rocket' ) );
		}

		$contents = $filesystem->get_contents( $file_path );

		if ( false === $contents ) {
			return new \WP_Error( 'file_not_read', __( 'The log file could not be read.', 'rocket' ) );
		}

		if ( $formatter instanceof HtmlFormatter ) {
			$entries = preg_split( '@<h1 @', $contents );
		} elseif ( $formatter instanceof LineFormatter ) {
			$entries = preg_split( '@^\[\d{4,}-\d{2,}-\d{2,} \d{2,}:\d{2,}:\d{2,}] @m', $contents );
		} else {
			$entries = 0;
		}

		$entries  = $entries ? number_format_i18n( count( $entries ) ) : '0';
		$bytes    = $filesystem->size( $file_path );
		$decimals = $bytes > pow( 1024, 3 ) ? 1 : 0;
		$bytes    = @size_format( $bytes, $decimals );
		$bytes    = str_replace( ' ', ' ', $bytes ); // Non-breaking space character.

		return compact( 'entries', 'bytes' );
	}

	/**
	 * Get the log file extension related to the formatter in use. This can be used when the file is downloaded.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string The corresponding file extension with the heading dot.
	 */
	public static function get_log_file_extension() {
		$formatter = static::get_stream_formatter();

		if ( ! $formatter ) {
			return '.log';
		}

		if ( $formatter instanceof HtmlFormatter ) {
			return '.html';
		}

		if ( $formatter instanceof LineFormatter ) {
			return '.txt';
		}

		return '.log';
	}

	/**
	 * Delete the log file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool True on success. False on failure.
	 */
	public static function delete_log_file() {
		$filesystem = \rocket_direct_filesystem();
		$file_path  = static::get_log_file_path();

		if ( ! $filesystem->exists( $file_path ) ) {
			return true;
		}

		$filesystem->put_contents( $file_path, '' );
		$filesystem->delete( $file_path, false, 'f' );

		return ! $filesystem->exists( $file_path );
	}

	/**
	 * Get the formatter used for the log file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return object|bool The formatter object on success. False on failure.
	 */
	public static function get_stream_formatter() {
		$handlers = static::get_logger()->getHandlers();

		if ( ! $handlers ) {
			return false;
		}

		foreach ( $handlers as $_handler ) {
			if ( $_handler instanceof StreamHandler ) {
				$handler = $_handler;
				break;
			}
		}

		if ( empty( $handler ) ) {
			return false;
		}

		return $handler->getFormatter();
	}


	/** ----------------------------------------------------------------------------------------- */
	/** CONSTANT ================================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if debug is enabled.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public static function debug_enabled() {
		return defined( 'WP_ROCKET_DEBUG' ) && WP_ROCKET_DEBUG;
	}

	/**
	 * Enable debug mode by adding a constant in the `wp-config.php` file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public static function enable_debug() {
		static::define_debug( true );
	}

	/**
	 * Disable debug mode by removing the constant in the `wp-config.php` file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public static function disable_debug() {
		static::define_debug( false );
	}

	/**
	 * Enable or disable debug mode by adding or removing a constant in the `wp-config.php` file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param bool $enable True to enable debug, false to disable.
	 */
	public static function define_debug( $enable ) {
		if ( $enable && static::debug_enabled() ) {
			// Debug is already enabled.
			return;
		}

		if ( ! $enable && ! static::debug_enabled() ) {
			// Debug is already disabled.
			return;
		}

		// Get the path to the file.
		$file_path = \rocket_find_wpconfig_path();

		if ( ! $file_path ) {
			// Couldn't get the path to the file.
			return;
		}

		// Get the content of the file.
		$filesystem = \rocket_direct_filesystem();
		$content    = $filesystem->get_contents( $file_path );

		if ( false === $content ) {
			// Cound't get the content of the file.
			return;
		}

		// Remove previous value.
		$placeholder = '## WP_ROCKET_DEBUG placeholder ##';
		$content     = preg_replace( '@^[\t ]*define\s*\(\s*["\']WP_ROCKET_DEBUG["\'].*$@miU', $placeholder, $content );
		$content     = preg_replace( "@\n$placeholder@", '', $content );

		if ( $enable ) {
			// Add the constant.
			$define  = "define( 'WP_ROCKET_DEBUG', true ); // Added by WP Rocket.\r\n";
			$content = preg_replace( '@<\?php\s*@i', "<?php\n$define", $content, 1 );
		}

		// Save the file.
		$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
		$filesystem->put_contents( $file_path, $content, $chmod );
	}


	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Remove cookies related to WP auth.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $cookies An array of cookies.
	 * @return array
	 */
	public static function remove_auth_cookies( $cookies = [] ) {
		if ( ! $cookies || ! is_array( $cookies ) ) {
			$cookies = $_COOKIE;
		}

		unset( $cookies['wordpress_test_cookie'] );

		if ( ! $cookies ) {
			return [];
		}

		$pattern = strtolower( '@^WordPress(?:user|pass|_sec|_logged_in)?_@' ); // Trolling PHPCS.

		foreach ( $cookies as $cookie_name => $value ) {
			if ( preg_match( $pattern, $cookie_name ) ) {
				unset( $cookies[ $cookie_name ] );
			}
		}

		return $cookies;
	}
}