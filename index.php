<?php
/**
 * Plugin Name:       The Events Calendar PRO Extension: Limit Time Range in Week View
 * Plugin URI:        https://theeventscalendar.com/extensions/events-calendar-pro-limit-time-range-in-week-view
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-limit-week-view-time-range
 * Description:       Adds option to WP Admin > Events > Settings > Display to set up the hour range shown on the week view.
 * Version:           1.0.1
 * Extension Class:   Tribe\Extensions\Limit_Week_View_Time_Range\Main
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-limit-week-view-time-range
 */

namespace Tribe\Extensions\Limit_Week_View_Time_Range;

use Tribe__Autoloader;
use Tribe__Dependency;
use Tribe__Extension;

/**
 * Define Constants
 */

if ( ! defined( __NAMESPACE__ . '\NS' ) ) {
	define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
}

if ( ! defined( \Tribe\Extensions\Limit_Week_View_Time_Range\NS . 'PLUGIN_TEXT_DOMAIN' ) ) {
	// `Tribe\Extensions\Example\PLUGIN_TEXT_DOMAIN` is defined
	define( NS . 'PLUGIN_TEXT_DOMAIN', 'tribe-ext-limit-week-view-time-range' );
}

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( Main::class )
) {

	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Main extends Tribe__Extension {

		/**
		 * @var Tribe__Autoloader
		 */
		private $class_loader;

		/**
		 * @var Settings
		 */
		private $settings;

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Pro__Main' );

			// Set the extension's TEC URL
			$this->set_url( 'https://theeventscalendar.com/extensions/limit-the-time-range-in-week-view/' );
		}

		/**
		 * Get this plugin's options prefix.
		 *
		 * Settings_Helper will append a trailing underscore before each option.
		 *
		 * @see \Tribe\Extensions\Example\Settings::set_options_prefix()
		 *
		 * @return string
		 */
		private function get_options_prefix() {
			return (string) str_replace( '-', '_', PLUGIN_TEXT_DOMAIN );
		}

		/**
		 * Get Settings instance.
		 *
		 * @return Settings
		 */
		private function get_settings() {
			if ( empty( $this->settings ) ) {
				$this->settings = new Settings( $this->get_options_prefix() );
			}

			return $this->settings;
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			load_plugin_textdomain( 'PLUGIN_TEXT_DOMAIN', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			$this->class_loader();

			$this->get_settings();

			add_filter( 'tribe_events_week_get_hours', [ $this, 'filter_week_hours' ] );
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @link https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/ All extensions require PHP 5.6+.
		 *
		 * @link https://secure.php.net/manual/en/migration56.new-features.php
		 * 5.6: Variadic Functions, Argument Unpacking, and Constant Expressions
		 *
		 * @return bool
		 */
		private function php_version_check() {
			$php_required_version = '5.6';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if (
					is_admin()
					&& current_user_can( 'activate_plugins' )
				) {
					$message = '<p>';
					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', PLUGIN_TEXT_DOMAIN ), $this->get_name(), $php_required_version );
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';

					tribe_notice( PLUGIN_TEXT_DOMAIN . '-php-version', $message, [ 'type' => 'error' ] );
				}

				return false;
			}

			return true;
		}

		/**
		 * Use Tribe Autoloader for all class files within this namespace in the 'src' directory.
		 *
		 * @return Tribe__Autoloader
		 */
		public function class_loader() {
			if ( empty( $this->class_loader ) ) {
				$this->class_loader = new Tribe__Autoloader;
				$this->class_loader->set_dir_separator( '\\' );
				$this->class_loader->register_prefix(
					NS,
					__DIR__ . DIRECTORY_SEPARATOR . 'src'
				);
			}

			$this->class_loader->register_autoloader();

			return $this->class_loader;
		}

		/**
		 * Get all of this extension's options.
		 *
		 * @return array
		 */
		public function get_all_options() {
			$settings = $this->get_settings();

			return $settings->get_all_options();
		}

		/**
		 * Filters the hours
		 *
		 * @param $hours
		 *
		 * @return mixed
		 */
		public function filter_week_hours( $hours ) {

			$options = $this->get_all_options();

			// Set the desired times here, pulls from settings
			$start_of_day = $options['start_time'];
			$end_of_day   = $options['end_time'];

			// Fallback
			if ( ! is_numeric( $start_of_day ) || $start_of_day < 0 || $start_of_day > 23 ) {
				$start_of_day = 0;
			}
			if ( ! is_numeric( $end_of_day ) || $end_of_day < 1 || $end_of_day > 23 || $end_of_day < $start_of_day ) {
				$end_of_day = 24;
			}

			foreach ( $hours as $hour => $formatted_hour ) {
				if ( $hour < $start_of_day || $hour >= $end_of_day ) {
					unset( $hours[ $hour ] );
				}
			}

			return $hours;
		}

	}
}