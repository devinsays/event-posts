<?php
/**
 * Template Name: Event Posts
 * Description: Example for displaying events.
 *
 * These are example queries for displaying event posts
 * on your site.
 */
 
?>

<?php
	// http://codex.wordpress.org/Function_Reference/current_time
	$current_time = current_time('mysql'); 
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $current_time );
	$current_timestamp = $today_year . $today_month . $today_day . $hour . $minute;
?>

<h1>Event Archive Page</h1>
<h3>Ordered by Publish Date</h3>

<p>If you are using an archive template like archive-event.php the following will diplay your most recent published event posts in order of their publish date.</p>

<?php

	if ( have_posts() ) :
		echo '<ul>';
		while ( have_posts() ) : the_post(); ?>
			<li>
			<h4><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
			<ul>
				<li>Publish date: <?php the_date('M d Y'); ?></li>
				<?php 
				// Gets the event start month from the meta field
				$month = get_post_meta( $post->ID, '_start_month', true );
				// Converts the month number to the month name
				$month = $wp_locale->get_month_abbrev( $wp_locale->get_month( $month ) );
				// Gets the event start day
				$day = get_post_meta( $post->ID, '_start_day', true );
				// Gets the event start year
				$year = get_post_meta( $post->ID, '_start_year', true );
				?>
				
				<li>Event start date: <?php echo $month . ' ' . $day . ' ' . $year; ?></li>
			</ul>
			</li>
		<?php endwhile;
		echo '</ul>';
	endif;
?>

<h1>Event Query</h1>
<h3>Ordered by Event Date</h3>

<p>The following query will display up to 20 event postings.  It will order them by the ept_event_date_start metabox and only display posts where the start date and time is after the current date and time.</p>

<p>The paging variables should be removed if the events query is not the primary loop.</p>

<?php

	// WordPress Paging Bug Fix
	// Use on archive or page templates if you posts_per_page
	// is different than the default set in the WordPress settings
	if ( get_query_var( 'paged' ) )
		$paged = get_query_var( 'paged' );
	elseif ( get_query_var( 'page' ) )
		$paged = get_query_var( 'page' );
	else
		$paged = 1;
		
	// Arguments for the modified query
	$args = array(
		'post_type' => 'event',
		'meta_key' => '_start_eventtimestamp',
		'orderby'=> 'meta_value_num',
		'meta_value' => $current_timestamp,
		'meta_compare' => '>=',
		'order' => 'ASC',
		'posts_per_page' => 20,
		'paged' => $paged
	);
	$events = new WP_Query( $args );

	if ( $events->have_posts() ) :
		echo '<ul>';
		while ( $events->have_posts() ) : $events->the_post(); ?>
			<li>
			<h4><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
			<ul>
				<li>Publish date: <?php the_date('M d Y'); ?></li>
				<?php 
				// Gets the event start month from the meta field
				$month = get_post_meta( $post->ID, '_start_month', true );
				// Converts the month number to the month name
				$month = $wp_locale->get_month_abbrev( $wp_locale->get_month( $month ) );
				// Gets the event start day
				$day = get_post_meta( $post->ID, '_start_day', true );
				// Gets the event start year
				$year = get_post_meta( $post->ID, '_start_year', true );
				?>
				
				<li>Event start date: <?php echo $month . ' ' . $day . ' ' . $year; ?></li>
				
				<li>Meta _start_eventtimestamp: <?php echo get_post_meta( $post->ID, '_start_eventtimestamp', true ); ?></li>
				<li>Current custom timestamp: <?php echo $current_timestamp; ?></li>
			</ul>
			</li>
		<?php endwhile;
		echo '</ul>';
	endif;
?>