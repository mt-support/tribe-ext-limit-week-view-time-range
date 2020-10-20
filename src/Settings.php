<?php

namespace Tribe\Extensions\Limit_Week_View_Time_Range;

use Tribe__Settings_Manager;

if ( ! class_exists( Settings::class ) ) {
	/**
	 * Do the Settings.
	 */
	class Settings {

		/**
		 * The Settings Helper class.
		 *
		 * @var Settings_Helper
		 */
		protected $settings_helper;

		/**
		 * The prefix for our settings keys.
		 *
		 * @see get_options_prefix() Use this method to get this property's value.
		 *
		 * @var string
		 */
		private $options_prefix = '';

		/**
		 * Settings constructor.
		 *
		 * @param string $options_prefix Recommended: the plugin text domain, with hyphens converted to underscores.
		 */
		public function __construct( $options_prefix ) {
			$this->settings_helper = new Settings_Helper();

			$this->set_options_prefix( $options_prefix );

			// Add settings specific to OSM
			add_action( 'admin_init', [ $this, 'add_settings' ] );
		}

		/**
		 * Allow access to set the Settings Helper property.
		 *
		 * @param Settings_Helper $helper
		 *
		 * @return Settings_Helper
		 * @see get_settings_helper()
		 *
		 */
		public function set_settings_helper( Settings_Helper $helper ) {
			$this->settings_helper = $helper;

			return $this->get_settings_helper();
		}

		/**
		 * Allow access to get the Settings Helper property.
		 *
		 * @see set_settings_helper()
		 */
		public function get_settings_helper() {
			return $this->settings_helper;
		}

		/**
		 * Set the options prefix to be used for this extension's settings.
		 *
		 * Recommended: the plugin text domain, with hyphens converted to underscores.
		 * Is forced to end with a single underscore. All double-underscores are converted to single.
		 *
		 * @param string $options_prefix
		 *
		 * @see get_options_prefix()
		 *
		 */
		private function set_options_prefix( $options_prefix ) {
			$options_prefix = $options_prefix . '_';

			$this->options_prefix = str_replace( '__', '_', $options_prefix );
		}

		/**
		 * Get this extension's options prefix.
		 *
		 * @return string
		 * @see set_options_prefix()
		 *
		 */
		public function get_options_prefix() {
			return $this->options_prefix;
		}

		/**
		 * Given an option key, get this extension's option value.
		 *
		 * This automatically prepends this extension's option prefix so you can just do `$this->get_option( 'a_setting' )`.
		 *
		 * @param string $key
		 *
		 * @param string $default
		 *
		 * @return mixed
		 * @see tribe_get_option()
		 *
		 */
		public function get_option( $key = '', $default = '' ) {
			$key = $this->sanitize_option_key( $key );

			return tribe_get_option( $key, $default );
		}

		/**
		 * Get an option key after ensuring it is appropriately prefixed.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		private function sanitize_option_key( $key = '' ) {
			$prefix = $this->get_options_prefix();

			if ( 0 === strpos( $key, $prefix ) ) {
				$prefix = '';
			}

			return $prefix . $key;
		}

		/**
		 * Get an array of all of this extension's options without array keys having the redundant prefix.
		 *
		 * @return array
		 */
		public function get_all_options() {
			$raw_options = $this->get_all_raw_options();

			$result = [];

			$prefix = $this->get_options_prefix();

			foreach ( $raw_options as $key => $value ) {
				$abbr_key            = str_replace( $prefix, '', $key );
				$result[ $abbr_key ] = $value;
			}

			return $result;
		}

		/**
		 * Get an array of all of this extension's raw options (i.e. the ones starting with its prefix).
		 *
		 * @return array
		 */
		public function get_all_raw_options() {
			$tribe_options = Tribe__Settings_Manager::get_options();

			if ( ! is_array( $tribe_options ) ) {
				return [];
			}

			$result = [];

			foreach ( $tribe_options as $key => $value ) {
				if ( 0 === strpos( $key, $this->get_options_prefix() ) ) {
					$result[ $key ] = $value;
				}
			}

			return $result;
		}

		/**
		 * Given an option key, delete this extension's option value.
		 *
		 * This automatically prepends this extension's option prefix so you can just do `$this->delete_option( 'a_setting' )`.
		 *
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function delete_option( $key = '' ) {
			$key = $this->sanitize_option_key( $key );

			$options = Tribe__Settings_Manager::get_options();

			unset( $options[ $key ] );

			return Tribe__Settings_Manager::set_options( $options );
		}

		/**
		 * Adds a new section of fields to Events > Settings > Display tab, appearing after the "Basic Template" section
		 * and before the "Date Format Settings" section.
		 */
		public function add_settings() {
			$start_hours = [
				0  => '00:00 (default)',
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
			];
			$end_hours   = [
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
				24 => '24:00 (default)',
			];

			$fields = [
				'heading'             => [
					'type' => 'html',
					'html' => $this->get_setting_intro_text(),
				],
				'start_time'          => [
					'type'            => 'dropdown',
					'options'         => $start_hours,
					'label'           => esc_html__( 'Start hour', 'tribe-ext-limit-week-view-time-range' ),
					'tooltip'         => $this->get_start_time_tooltip(),
					'default'         => '0',
					'validation_type' => 'html',
				],
				'end_time'            => [
					'type'            => 'dropdown',
					'options'         => $end_hours,
					'label'           => esc_html__( 'End hour', 'tribe-ext-limit-week-view-time-range' ),
					'tooltip'         => $this->get_end_time_tooltip(),
					'default'         => '24',
					'validation_type' => 'html',
				],
				'sidebar_time_format' => [
					'type'            => 'dropdown',
					'options'         => $this->get_sidebar_time_format(),
					'label'           => esc_html__( 'Time format', 'tribe-ext-limit-week-view-time-range' ),
					'tooltip'         => $this->get_sidebar_time_format_tooltip(),
					'default'         => 'g a',
					'validation_type' => 'html',
				],
				'show_grid' => [
					'type'            => 'checkbox_bool',
					'label'           => esc_html__( 'Show grid', 'tribe-ext-limit-week-view-time-range' ),
					'tooltip'         => $this->get_show_grid_tooltip(),
					'validation_type' => 'boolean',
				],
			];

			/**
			 * Remove 'sidebar_time_format' if using V1
			 */
			if ( ! function_exists( 'tribe_events_views_v2_is_enabled' ) || ( function_exists( 'tribe_events_views_v2_is_enabled' ) && empty( tribe_events_views_v2_is_enabled() ) ) ) {
				unset( $fields['sidebar_time_format'] );
				unset( $fields['show_grid'] );
			}

			$this->settings_helper->add_fields( $this->prefix_settings_field_keys( $fields ),
			                                    'display',
			                                    'enable_month_view_cache',
			                                    false );
		}

		private function get_sidebar_time_format() {
			$time_format = [
				'g a'   => '3 pm (default)',
				'g:i a' => '3:00 pm',
				'g A'   => '3 PM',
				'G'     => '15',
				'G:i'   => '15:00',
				'Hi'    => '1500',
			];

			return $time_format;
		}

		/**
		 * Add the options prefix to each of the array keys.
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		private function prefix_settings_field_keys( array $fields ) {
			$prefixed_fields = array_combine( array_map( function ( $key ) {
				return $this->get_options_prefix() . $key;
			},
				array_keys( $fields ) ),
			                                  $fields );

			return (array) $prefixed_fields;
		}

		/**
		 * Here is an example of getting some HTML for the Settings Header.
		 *
		 * @return string
		 */
		private function get_setting_intro_text() {
			$result = '<h3>' . esc_html_x( 'Limit Week View Time Range',
			                               'Settings header',
			                               'tribe-ext-limit-week-view-time-range' ) . '</h3>';
			$result .= '<div style="margin-left: 20px;">';
			$result .= '<p>';
			$result .= esc_html_x( 'Set up the time range your week view should show. The start hour should be earlier than the end hour.',
			                       'Settings',
			                       'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html_x( 'It is recommended to have at least 8-9 hours between the start hour and the end hour.',
			                       'Settings',
			                       'tribe-ext-limit-week-view-time-range' );
			$result .= '</p>';
			$result .= '</div>';

			return $result;
		}

		/**
		 * Tooltip for the start time option field
		 *
		 * @return string
		 */
		private function get_start_time_tooltip() {
			$result = esc_html__( 'Select a time within the range of 00:00-23:00',
			                      'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html__( 'Events starting before this time will not show up.',
			                       'tribe-ext-limit-week-view-time-range' );

			return $result;
		}

		/**
		 * Tooltip for the end time option field
		 *
		 * @return string
		 */
		private function get_end_time_tooltip() {
			$result = esc_html__( 'Select a time within the range of 01:00-24:00',
			                      'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html__( 'Events starting after this time will not show up.',
			                       'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html__( 'Events ending after this time might not show up or might cause a small visual glitch.',
			                       'tribe-ext-limit-week-view-time-range' );

			return $result;
		}

		/**
		 * Tooltip for the end time option field
		 *
		 * @return string
		 */
		private function get_sidebar_time_format_tooltip() {
			$result = esc_html__( 'The time format that should be used at the side of the week grid.',
			                      'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html__( 'Requires updated (V2) calendar design.', 'tribe-ext-limit-week-view-time-range' );

			return $result;
		}

		/**
		 * Tooltip for showing the grid
		 *
		 * @return string
		 */
		private function get_show_grid_tooltip() {
			$result = esc_html__( 'Enabling this option will show a grid (light grey dashed horizontal lines at every hour) on week view.',
			                      'tribe-ext-limit-week-view-time-range' );
			$result .= '<br/>';
			$result .= esc_html__( 'Requires updated (V2) calendar design.', 'tribe-ext-limit-week-view-time-range' );

			return $result;
		}

	} // class
}
