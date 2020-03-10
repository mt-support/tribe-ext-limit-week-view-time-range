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

		protected $opts_prefix = 'tribe_ext_lwh_opts_';

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Pro__Main' );

			// Set the extension's TEC URL
			$this->set_url( 'https://theeventscalendar.com/extensions/events-calendar-pro-limit-time-range-in-week-view/' );
		}

		/**
		 * Adds settings options
		 */
		public function add_settings() {
			if ( ! class_exists( 'Tribe__Extension__Settings_Helper' ) ) {
				require_once dirname( __FILE__ ) . '/src/Tribe/Settings_Helper.php';
			}

			$start_hours = array(
				0  => '00:00',
				1  => '01:00',
				2  => '02:00',
				3  => '03:00',
				4  => '04:00',
				5  => '05:00',
				6  => '06:00',
				7  => '07:00',
				8  => '08:00',
				9  => '09:00',
				10 => '10:00',
				11 => '11:00',
				12 => '12:00',
				13 => '13:00',
				14 => '14:00',
				15 => '15:00',
				16 => '16:00',
				17 => '17:00',
				18 => '18:00',
				19 => '19:00',
				20 => '20:00',
				21 => '21:00',
				22 => '22:00',
				23 => '23:00'
			);
			$end_hours   = array(
				1  => '01:00',
				2  => '02:00',
				3  => '03:00',
				4  => '04:00',
				5  => '05:00',
				6  => '06:00',
				7  => '07:00',
				8  => '08:00',
				9  => '09:00',
				10 => '10:00',
				11 => '11:00',
				12 => '12:00',
				13 => '13:00',
				14 => '14:00',
				15 => '15:00',
				16 => '16:00',
				17 => '17:00',
				18 => '18:00',
				19 => '19:00',
				20 => '20:00',
				21 => '21:00',
				22 => '22:00',
				23 => '23:00',
				24 => '23:59'
			);

			$setting_helper = new Tribe__Settings_Helper();

			$fields = array(
				$this->opts_prefix . 'heading'     => array(
					'type' => 'html',
					'html' => '<h3>' . esc_html__( 'Limit Week View Time Range', 'PLUGIN_TEXT_DOMAIN' ) . '</h3>',
				),
				$this->opts_prefix . 'helper_text' => array(
					'type' => 'html',
					'html' => '<p>' . esc_html__( 'Set up the time range your week view should show. The start hour should be lower than the end hour.', 'PLUGIN_TEXT_DOMAIN' ) . '</p>',
				),
				$this->opts_prefix . 'start_time'  => array(
					'type'            => 'dropdown',
					'options'         => $start_hours,
					'label'           => esc_html__( 'Start hour', 'PLUGIN_TEXT_DOMAIN' ),
					'tooltip'         => '00:00-23:00',
					'validation_type' => 'html',
				),
				$this->opts_prefix . 'end_time'    => array(
					'type'            => 'dropdown',
					'options'         => $end_hours,
					'label'           => esc_html__( 'End hour', 'PLUGIN_TEXT_DOMAIN' ),
					'tooltip'         => '01:00-23:59',
					'validation_type' => 'html',
				),
			);

			$setting_helper->add_fields( $fields, 'display', 'enable_month_view_cache', false );
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

			add_action( 'admin_init', array( $this, 'add_settings' ) );
			add_filter( 'tribe_events_week_get_hours', array( $this, 'filter_week_hours' ) );
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @link https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/ All extensions require PHP 5.6+.
		 *
		 * Delete this paragraph and the non-applicable comments below.
		 * Make sure to match the readme.txt header.
		 *
		 * Note that older version syntax errors may still throw fatals even
		 * if you implement this PHP version checking so QA it at least once.
		 *
		 * @link https://secure.php.net/manual/en/migration56.new-features.php
		 * 5.6: Variadic Functions, Argument Unpacking, and Constant Expressions
		 *
		 * @link https://secure.php.net/manual/en/migration70.new-features.php
		 * 7.0: Return Types, Scalar Type Hints, Spaceship Operator, Constant Arrays Using define(), Anonymous Classes, intdiv(), and preg_replace_callback_array()
		 *
		 * @link https://secure.php.net/manual/en/migration71.new-features.php
		 * 7.1: Class Constant Visibility, Nullable Types, Multiple Exceptions per Catch Block, `iterable` Pseudo-Type, and Negative String Offsets
		 *
		 * @link https://secure.php.net/manual/en/migration72.new-features.php
		 * 7.2: `object` Parameter and Covariant Return Typing, Abstract Function Override, and Allow Trailing Comma for Grouped Namespaces
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
		 * Filters the hours
		 *
		 * @param $hours
		 *
		 * @return mixed
		 */
		public function filter_week_hours( $hours ) {

			// Set the desired times here, pulls from settings
			$start_of_day = tribe_get_option( $this->opts_prefix . 'start_time' );
			$end_of_day   = tribe_get_option( $this->opts_prefix . 'end_time' );

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