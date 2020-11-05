<?php
/**
 * View: Week View - Event
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/week/grid-body/events-day/event.php
 *
 * This template override is needed to make the The Events Calendar Pro Extension: Limit Week View Time Range
 * work with the updated (V2) designs.
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post $event       The event post object with properties added by the `tribe_get_event` function.
 * @var array   $ext_options An array of the extension settings.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

use Tribe__Date_Utils as Dates;

$classes = [ 'tribe-events-pro-week-grid__event' ];

if ( ! empty( $event->is_past ) ) {
	$classes[] = 'tribe-events-pro-week-grid__event--past';
}

if ( ! empty( $event->featured ) ) {
	$classes[] = 'tribe-events-pro-week-grid__event--featured';
}

/*
 * Some CSS classes (i.e. vertical position, duration and sequence) have been calculated in the Week View.
 * Here we add them to the ones that should be applied to the event element.
 */
$classes = array_merge( array_values( $classes ), array_values( $event->classes ) );
$classes = get_post_class( $classes, $event->ID );
$data_js = [ 'tribe-events-pro-week-grid-event-link', 'tribe-events-tooltip' ];

/**
 * Get start time in seconds
 */
$start_time = Dates::time_between( $event->dates->start->format( 'Y-m-d 0:0:0' ), $event->dates->start->format( Dates::DBDATETIMEFORMAT ) );

/**
 * Get the extension settings
 */
$grid_start_time = (int) $ext_options['grid_start_time'];
$grid_end_time   = (int) $ext_options['grid_end_time'];

/**
 * int containing if we have found the start time of an event based on the CSS class.
 */
$found_start_time_class = false;

/**
 * The pattern of the CSS class of the vertical offset.
 */
$pattern = '/(tribe-events-pro-week-grid__event--t-)/';

foreach ( $classes as $key => $class ) {
	// Check if event has the vertical offset class.
	// An event starting at 12am doesn't have a vertical offset class.
	if ( preg_match( $pattern, $class ) ) {
		$found_start_time_class = true;
	}
}

// Hide the ones that would be in the 12 to 1 row. (They don't have the class.)
if (
	! $found_start_time_class
	&& $grid_start_time > 0
) {
	$classes[] = 'tribe-common-a11y-visual-hide';
} else {
	// Set the new class
	foreach ( $classes as $key => $class ) {
		$vertical_offset_class = strpos( $class, 'tribe-events-pro-week-grid__event--t-' );

		if ( preg_match( $pattern, $class ) ) {
			// Remove the old vertical positioning
			unset( $classes[ $key ] );

			// Grab the vertical offset
			$event_start_time = str_replace( 'tribe-events-pro-week-grid__event--t-', '', $class );
			$time_split       = explode( '-', $event_start_time );

			// Set the new starting hour / offset of the event
			$new_event_start_hour = (int) $time_split[0] - $grid_start_time;

			// Hide if...
			if (
				// ... time is off the chart (negative start time).
				$new_event_start_hour <= 0
				// ... time is in the first 15 minutes of the grid.
				|| (
					$grid_start_time === $new_event_start_hour
					&& (int) $time_split[1] <= 15
				)
				// ... original time is before the grid start time.
				|| (int) $time_split[0] < $grid_start_time
				// ... time + 1 hour is after the grid end time (for long events at the end of the day).
				|| $grid_end_time <= $new_event_start_hour + $grid_start_time + 1
			) {
				$classes[ $key ] = 'tribe-common-a11y-visual-hide';
				break;
			}

			$time_split[0]   = $new_event_start_hour;
			$classes[ $key ] = 'tribe-events-pro-week-grid__event--t-' . implode( '-', $time_split );
		}
	}
}
?>
<article
	<?php tribe_classes( $classes ) ?>
	data-js="tribe-events-pro-week-grid-event"
	data-start-time="<?php echo esc_attr( $start_time ); ?>"
	data-event-id="<?php echo esc_attr( $event->ID ); ?>"
>
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		class="tribe-events-pro-week-grid__event-link"
		data-js="<?php echo esc_attr( implode( ' ', $data_js ) ); ?>"
		data-tooltip-content="#tribe-events-tooltip-content-<?php echo esc_attr( $event->ID ); ?>"
		aria-describedby="tribe-events-tooltip-content-<?php echo esc_attr( $event->ID ); ?>"
	>
		<div class="tribe-events-pro-week-grid__event-link-inner">

			<?php $this->template( 'week/grid-body/events-day/event/featured-image', [ 'event' => $event ] ); ?>
			<?php $this->template( 'week/grid-body/events-day/event/date', [ 'event' => $event ] ); ?>
			<?php $this->template( 'week/grid-body/events-day/event/title', [ 'event' => $event ] ); ?>

		</div>
	</a>
</article>

<?php $this->template( 'week/grid-body/events-day/event/tooltip', [ 'event' => $event ] ); ?>
