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
					'html' => '<h3>' . esc_html__( 'Limit Week View Time Range', 'tribe-ext-limit-week-view-time-range' ) . '</h3>',
				),
				$this->opts_prefix . 'helper_text' => array(
					'type' => 'html',
					'html' => '<p>' . esc_html__( 'Set up the time range your week view should show. The start hour should be lower than the end hour.', 'tribe-ext-limit-week-view-time-range' ) . '</p>',
				),
				$this->opts_prefix . 'start_time'  => array(
					'type'            => 'dropdown',
					'options'         => $start_hours,
					'label'           => esc_html__( 'Start hour', 'tribe-ext-limit-week-view-time-range' ),
					'tooltip'         => '00:00-23:00',
					'validation_type' => 'html',
				),
				$this->opts_prefix . 'end_time'    => array(
					'type'            => 'dropdown',
					'options'         => $end_hours,
					'label'           => esc_html__( 'End hour', 'tribe-ext-limit-week-view-time-range' ),
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
			load_plugin_textdomain( 'tribe-ext-limit-week-view-time-range', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			add_action( 'admin_init', array( $this, 'add_settings' ) );
			add_filter( 'tribe_events_week_get_hours', array( $this, 'filter_week_hours' ) );
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