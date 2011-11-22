<?php
/**
 * Template Name: Event Posts
 * Description: Example for displaying events.
 *
 * These are example queries for displaying event posts
 * on your site.
 */
 
?>

<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<title>Event Archive Page</title>
</head>
<body>

<h1>Event Archive Page</h1>
<h3>Ordered by Event Start Date</h3>

<p>The code that alters the regular query behavior is in <a href="https://github.com/devinsays/event-posts/blob/master/event-posts.php">event-posts.php</a> in the function ep_event_query.</p>

<?php

	// http://codex.wordpress.org/Function_Reference/current_time
	$current_time = current_time('mysql'); 
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $current_time );
	$current_timestamp = $today_year . $today_month . $today_day . $hour . $minute;

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
				<li>Meta _start_eventtimestamp: <?php echo get_post_meta( $post->ID, '_start_eventtimestamp', true ); ?></li>
				<li>Current custom timestamp: <?php echo $current_timestamp; ?></li>
			</ul>
			</li>
		<?php endwhile;
		echo '</ul>';
	endif;
?>

<?php if ( get_next_posts_link() ) : ?>
	<div class="nav-previous"><?php next_posts_link( 'There\'s More Events <span class="meta-nav">&rarr;</span> ' ); ?></div>
<?php endif; ?>

<?php if ( get_previous_posts_link() ) : ?>
	<div class="nav-next"><?php previous_posts_link( '<span class="meta-nav">&larr;</span> Events You\'re About to Miss' ); ?></div>
<?php endif; ?>

<h3>Separate WP_Query</h3>

<p>This displays up to 20 event posts as a list.</p>

<?php
	 $args = array( 'post_type' => 'event',
	'meta_key' => '_start_eventtimestamp',
	'orderby'=> 'meta_value_num',
	'order' => 'ASC',
	'posts_per_page' => 20,
	 );
	 $events = new WP_Query( $args );
	
	if ( $events->have_posts() ) :
		echo '<ul>';
		while ( $events->have_posts() ) : $events->the_post();
			echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
		endwhile;
		echo '</ul>';
	endif;
?>

</body>
</html>