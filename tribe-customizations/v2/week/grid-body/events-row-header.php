<?php
/**
 * View: Week View - Events Row Header
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/week/grid-body/events-row-header.php
 *
 * This template override is needed to make the The Events Calendar Pro Extension: Limit Week View Time Range V2
 * work with the updated (V2) designs.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version 5.0.0
 *
 * @var array $ext_options An array of the extension settings.
 */

/**
 * Filters the time format in the week view row header.
 *
 * @since 2.0.0
 *
 * @param string Time format.
 */
$time_format = apply_filters(
	'tribe_events_week_sidebar_time_format',
	tribe_get_option(
		'tribe_ext_limit_week_view_time_range_sidebar_time_format',
		'g a'
	)
);
?>

<div class="tribe-events-pro-week-grid__events-row-header" role="rowheader">

	<?php
	// Set up the new header
	for ( $i = (int) $ext_options['grid_start_time']; $i <= (int) $ext_options['grid_end_time']; $i ++ ) {
		$classes[] = 'tribe-events-pro-week-grid__events-time-tag';
		$dt        = $i . ':00';
		$label     = date( $time_format, strtotime( $dt ) );

		// First header
		if ( $i == $ext_options['grid_start_time'] ) {
			$classes[] = 'tribe-events-pro-week-grid__events-time-tag--first';
			$classes[] = 'tribe-common-a11y-visual-hide';
		}

		// Last header
		if ( $i == $ext_options['grid_end_time'] ) {
			$classes[] = 'tribe-events-pro-week-grid__events-time-tag--last';
			$classes[] = ' tribe-common-a11y-visual-hide';
		}

		echo '<time class="' . implode( ' ', $classes ) . '" datetime="' . $dt . '">' . $label . '</time>';

		unset( $classes );
	}
	?>
</div>
