<?php
/**
 * View: Week View - Grid Body
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/week/grid-body.php
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
 * @var array $multiday_events An array of each day multi-day events and more event count, if any, in the shape
 *                                 `[ <Y-m-d> => [ 'events' => [ ...$multiday_events], 'more_events' => <int> ] ]`.
 * @var bool $has_multiday_events Boolean whether the week has multiday events or not.
 * @var array $events An array of each day non multi-day events, if any, in the shape `[ <Y-m-d> => [ ...$events ] ]`.
 */
?>
<?php
/**
 * Get the settings
 */
$ext_options['grid_start_time'] = tribe_get_option( 'tribe_ext_limit_week_view_time_range_start_time', '0' );
$ext_options['grid_end_time']   = tribe_get_option( 'tribe_ext_limit_week_view_time_range_end_time', '24' );
$ext_options['show_grid']       = tribe_get_option( 'tribe_ext_limit_week_view_time_range_show_grid', false );
?>

<div class="tribe-events-pro-week-grid__body" role="rowgroup">

	<?php if ( count( $multiday_events ) && $has_multiday_events ) : ?>

		<div class="tribe-events-pro-week-grid__multiday-events-row-outer-wrapper">
			<div class="tribe-events-pro-week-grid__multiday-events-row-wrapper">
				<div
						class="tribe-events-pro-week-grid__multiday-events-row"
						data-js="tribe-events-pro-week-multiday-events-row"
						role="row"
				>

					<?php $this->template( 'week/grid-body/multiday-events-row-header' ); ?>

					<?php foreach ( $multiday_events as $day => list( $day_multiday_events, $more_events ) ) : ?>
						<?php $this->template( 'week/grid-body/multiday-events-day',
						                       [ 'day'         => $day,
						                         'events'      => $day_multiday_events,
						                         'more_events' => $more_events
						                       ] ); ?>
					<?php endforeach; ?>

				</div>
			</div>
		</div>

	<?php endif; ?>

	<div class="tribe-events-pro-week-grid__events-scroll-wrapper">
		<div class="tribe-events-pro-week-grid__events-row-outer-wrapper" data-js="tribe-events-pro-week-grid-events-row-outer-wrapper">
			<div class="tribe-events-pro-week-grid__events-row-wrapper" data-js="tribe-events-pro-week-grid-events-row-wrapper">
				<div class="tribe-events-pro-week-grid__events-row" role="row">

					<?php $this->template( 'week/grid-body/events-row-header', [ 'ext_options' => $ext_options ] ); ?>

					<?php
					$count = 0;
					foreach ( $events as $day => $day_events ) :
						$this->template( 'week/grid-body/events-day',
						                 [
							                 'events'      => $day_events,
							                 'ext_options' => $ext_options,
							                 'count'       => $count
						                 ] );
						$count++;
					endforeach;
					?>

				</div>
			</div>
		</div>
	</div>

</div>
