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
 * @link http://m.tri.be/1aiy
 *
 * @version 5.0.0
 */

?>
<div class="tribe-events-pro-week-grid__events-row-header" role="rowheader">

	<?php
	// Get the settings
	$start       = tribe_get_option( 'tribe_ext_limit_week_view_time_range_start_time', '0' );
	$finish      = tribe_get_option( 'tribe_ext_limit_week_view_time_range_end_time', '24' );
	$time_format = apply_filters( 'tribe_events_week_sidebar_time_format',
	                              tribe_get_option( 'tribe_ext_limit_week_view_time_range_sidebar_time_format',
	                                                'g a' ) );

	// Set up the new header
	for ( $i = $start; $i <= $finish; $i++ ) {
		$classes[] = 'tribe-events-pro-week-grid__events-time-tag';
		$dt        = $i . ':00';
		$label     = date( $time_format, mktime( $i, 0, 0, 1, 1, 2020 ) );

		// First header
		if ( $i == $start ) {
			$classes[] = 'tribe-events-pro-week-grid__events-time-tag--first';
			$classes[] = 'tribe-common-a11y-visual-hide';
		}
		// Last header
		if ( $i == $finish ) {
			$classes[] = 'tribe-events-pro-week-grid__events-time-tag--last';
			$classes[] = ' tribe-common-a11y-visual-hide';
		}
		echo '<time class="' . implode( ' ', $classes ) . '" datetime="' . $dt . '">' . $label . '</time>';
		unset( $classes );
	}
	?>
</div>
