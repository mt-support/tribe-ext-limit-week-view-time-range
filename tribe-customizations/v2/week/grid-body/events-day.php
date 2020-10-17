<?php
/**
 * View: Week View - Events Day
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/week/grid-body/events-day.php
 *
 * This template override is needed to make the The Events Calendar Pro Extension: Limit Week View Time Range
 * work with the updated (V2) designs.
 *
 * See more documentation about our views templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post[] $events The day events post objects.
 *
 * @see tribe_get_event() for the additional properties added to the event post object.
 */
?>

<div class="tribe-events-pro-week-grid__events-day" role="gridcell">
	<?php
	if ( $count == 0 && $ext_options['show_grid'] ) {
		for ( $i = 0; $i <= $ext_options['grid_end_time'] - $ext_options['grid_start_time']; $i++ ) {
			echo '<div class="tribe-events-pro-week-grid__events-day-gridlines" style="top: calc(' . $i . ' * 48px);"></div>';
		}
	}
	?>

	<?php foreach ( $events as $event ) : ?>
		<?php $this->setup_postdata( $event ); ?>
		<?php $this->template( 'week/grid-body/events-day/event', [ 'event' => $event, 'ext_options' => $ext_options ] ); ?>
	<?php endforeach; ?>
</div>
