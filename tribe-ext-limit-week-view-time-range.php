<?php
/**
 * Plugin Name:       The Events Calendar Pro Extension: Limit Week View Time Range
 * Plugin URI:        https://theeventscalendar.com/extensions/limit-the-time-range-in-week-view/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-limit-week-view-time-range
 * Description:       Limit the hour range shown on the week view.
 * Version:           2.0.0
 * Extension Class:   Tribe\Extensions\Limit_Week_View_Time_Range\Main
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-limit-week-view-time-range
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

namespace Tribe\Extensions\Limit_Week_View_Time_Range;

use Tribe__Autoloader;
use Tribe__Extension;
use Tribe__Template;

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
			$this->add_required_plugin( 'Tribe__Events__Pro__Main', '5.0' );
		}

		/**
		 * Get this plugin's options prefix.
		 *
		 * Settings_Helper will append a trailing underscore before each option.
		 *
		 * @see \Tribe\Extensions\Limit_Week_View_Time_Range\Settings::set_options_prefix()
		 *
		 * @return string
		 */
		private function get_options_prefix() {
			return (string) str_replace( '-', '_', 'tribe-ext-limit-week-view-time-range' );
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
			// Load plugin textdomain
			load_plugin_textdomain( 'tribe-ext-limit-week-view-time-range', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			$this->class_loader();

			$this->get_settings();

			add_filter( 'tribe_events_week_get_hours', [ $this, 'filter_week_hours' ] );
			add_filter( 'tribe_template_path_list', [ $this, 'alternative_week_view_template_locations' ], 10, 2 );
			add_action( 'wp_enqueue_scripts', [ $this, 'enquque_styles' ] );
		}

		/**
		 * Template override in plugin folder. Needed for V2.
		 *
		 * @param                  $folders
		 * @param Tribe__Template $template
		 *
		 * @return mixed
		 */
		function alternative_week_view_template_locations( $folders, Tribe__Template $template ) {
			// Which file namespace your plugin will use.
			$plugin_name = 'tribe-ext-limit-week-view-time-range';

			// Which order we should load your plugin files at. Plugin in which the file was loaded from = 20. Events Pro = 25. Tickets = 17.
			$priority = 5;

			// Which folder in your plugin the customizations will be loaded from.
			$custom_folder[] = 'tribe-customizations';

			// Builds the correct file path to look for.
			$plugin_path = array_merge(
				(array) trailingslashit( plugin_dir_path( __FILE__ ) ),
				(array) $custom_folder,
				array_diff( $template->get_template_folder(), [ 'src', 'views' ] )
			);

			/*
			 * Custom loading location for overwriting file loading.
			 */
			$folders[ $plugin_name ] = [
				'id'        => $plugin_name,
				'namespace' => $plugin_name, // Only set this if you want to overwrite theme namespacing
				'priority'  => $priority,
				'path'      => $plugin_path,
			];

			return $folders;
		}

		/**
		 * Filters the hours of legacy (V1) week view.
		 *
		 * @param array $hours
		 *
		 * @return array
		 */
		public function filter_week_hours( array $hours ) {
			$options = $this->get_all_options();

			// Set the desired times here, pulls from settings
			$start_of_day = $options['start_time'];
			$end_of_day   = $options['end_time'];

			// Fallback
			if (
				! is_numeric( $start_of_day )
				|| $start_of_day < 0
				|| $start_of_day > 23
			) {
				$start_of_day = 0;
			}

			if (
				! is_numeric( $end_of_day )
				|| $end_of_day < 1
				|| $end_of_day > 23
				|| $end_of_day < $start_of_day
			) {
				$end_of_day = 24;
			}

			foreach ( $hours as $hour => $formatted_hour ) {
				if (
					$hour < $start_of_day
					|| $hour >= $end_of_day
				) {
					unset( $hours[ $hour ] );
				}
			}

			return $hours;
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @link https://theeventscalendar.com/knowledgebase/php-version-requirement-changes/ All extensions require PHP 5.6+.
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
					$message .= sprintf(
						__(
							'%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.',
							'tribe-ext-limit-week-view-time-range'
						),
						$this->get_name(),
						$php_required_version
					);
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( 'tribe-ext-limit-week-view-time-range-php-version', $message, [ 'type' => 'error' ] );
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
					__NAMESPACE__ . '\\',
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
		 * Get a specific extension option.
		 *
		 * @param        $option
		 * @param string $default
		 *
		 * @return array
		 */
		public function get_option( $option, $default = '' ) {
			$settings = $this->get_settings();

			return $settings->get_option( $option, $default );
		}

		/**
		 * Enqueuing stylesheet
		 */
		public function enquque_styles() {
			if ( function_exists( 'tribe_events_views_v2_is_enabled' ) && ! empty( tribe_events_views_v2_is_enabled() ) ) {
				wp_enqueue_style(
					'tribe-ext-limit-week-view-time-range',
					plugin_dir_url( __FILE__ ) . 'src/resources/style.css'
				);
			}
		}

	} // end class
} // end if class_exists check
