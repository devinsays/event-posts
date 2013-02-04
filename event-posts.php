<?php
/*
Plugin Name:Event Posts
Plugin URI: http://www.wptheming.com
Description: Creates a custom post type for events with associated metaboxes.
Version: 0.1
Author: Devin Price
Author URI: http://www.wptheming.com
License: GPLv2 or later
*/

/**
 * Flushes rewrite rules on plugin activation to ensure event posts don't 404
 * http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
 */

function ep_eventposts_activation() {
	ep_eventposts();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'ep_eventposts_activation' );

function ep_eventposts() {

	/**
	 * Enable the event custom post type
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */

	$labels = array(
		'name' => __( 'Events', 'eventposttype' ),
		'singular_name' => __( 'Event', 'eventposttype' ),
		'add_new' => __( 'Add New Event', 'eventposttype' ),
		'add_new_item' => __( 'Add New Event', 'eventposttype' ),
		'edit_item' => __( 'Edit Event', 'eventposttype' ),
		'new_item' => __( 'Add New Event', 'eventposttype' ),
		'view_item' => __( 'View Event', 'eventposttype' ),
		'search_items' => __( 'Search Events', 'eventposttype' ),
		'not_found' => __( 'No events found', 'eventposttype' ),
		'not_found_in_trash' => __( 'No events found in trash', 'eventposttype' )
	);

	$args = array(
    	'labels' => $labels,
    	'public' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'comments' ),
		'capability_type' => 'post',
		'rewrite' => array("slug" => "event"), // Permalinks format
		'menu_position' => 5,
		'menu_icon' => plugin_dir_url( __FILE__ ) . '/images/calendar-icon.gif',  // Icon Path
		'has_archive' => true
	); 

	register_post_type( 'event', $args );
}

add_action( 'init', 'ep_eventposts' );

/**
 * Adds event post metaboxes for start time and end time
 * http://codex.wordpress.org/Function_Reference/add_meta_box
 *
 * We want two time event metaboxes, one for the start time and one for the end time.
 * Two avoid repeating code, we'll just pass the $identifier in a callback.
 * If you wanted to add this to regular posts instead, just swap 'event' for 'post' in add_meta_box.
 */

function ep_eventposts_metaboxes() {
	add_meta_box( 'ept_event_date_start', 'Start Date and Time', 'ept_event_date', 'event', 'side', 'default', array( 'id' => '_start') );
	add_meta_box( 'ept_event_date_end', 'End Date and Time', 'ept_event_date', 'event', 'side', 'default', array('id'=>'_end') );
	add_meta_box( 'ept_event_location', 'Event Location', 'ept_event_location', 'event', 'normal', 'default', array('id'=>'_end') );
}
add_action( 'admin_init', 'ep_eventposts_metaboxes' );

// Metabox HTML

function ept_event_date($post, $args) {
	$metabox_id = $args['args']['id'];
	global $post, $wp_locale;

	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'ep_eventposts_nonce' );

	$time_adj = current_time( 'timestamp' );
	$month = get_post_meta( $post->ID, $metabox_id . '_month', true );

	if ( empty( $month ) ) {
		$month = gmdate( 'm', $time_adj );
	}

	$day = get_post_meta( $post->ID, $metabox_id . '_day', true );

	if ( empty( $day ) ) {
		$day = gmdate( 'd', $time_adj );
	}

	$year = get_post_meta( $post->ID, $metabox_id . '_year', true );

	if ( empty( $year ) ) {
		$year = gmdate( 'Y', $time_adj );
	}
	
	$hour = get_post_meta($post->ID, $metabox_id . '_hour', true);
 
    if ( empty($hour) ) {
        $hour = gmdate( 'H', $time_adj );
    }
 
    $min = get_post_meta($post->ID, $metabox_id . '_minute', true);
 
    if ( empty($min) ) {
        $min = '00';
    }

	$month_s = '<select name="' . $metabox_id . '_month">';
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$month_s .= "\t\t\t" . '<option value="' . zeroise( $i, 2 ) . '"';
		if ( $i == $month )
			$month_s .= ' selected="selected"';
		$month_s .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month_s .= '</select>';

	echo $month_s;
	echo '<input type="text" name="' . $metabox_id . '_day" value="' . $day  . '" size="2" maxlength="2" />';
    echo '<input type="text" name="' . $metabox_id . '_year" value="' . $year . '" size="4" maxlength="4" /> @ ';
    echo '<input type="text" name="' . $metabox_id . '_hour" value="' . $hour . '" size="2" maxlength="2"/>:';
    echo '<input type="text" name="' . $metabox_id . '_minute" value="' . $min . '" size="2" maxlength="2" />';
 
}

function ept_event_location() {
	global $post;
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'ep_eventposts_nonce' );
	// The metabox HTML
	$event_location = get_post_meta( $post->ID, '_event_location', true );
	echo '<label for="_event_location">Location:</label>';
	echo '<input type="text" name="_event_location" value="' . $event_location  . '" />';
}

// Save the Metabox Data

function ep_eventposts_save_meta( $post_id, $post ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( !isset( $_POST['ep_eventposts_nonce'] ) )
		return;

	if ( !wp_verify_nonce( $_POST['ep_eventposts_nonce'], plugin_basename( __FILE__ ) ) )
		return;

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ) )
		return;

	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though
	
	$metabox_ids = array( '_start', '_end' );

	foreach ($metabox_ids as $key ) {
	    
	    $aa = $_POST[$key . '_year'];
		$mm = $_POST[$key . '_month'];
		$jj = $_POST[$key . '_day'];
		$hh = $_POST[$key . '_hour'];
		$mn = $_POST[$key . '_minute'];
		
		$aa = ($aa <= 0 ) ? date('Y') : $aa;
		$mm = ($mm <= 0 ) ? date('n') : $mm;
		$jj = sprintf('%02d',$jj);
		$jj = ($jj > 31 ) ? 31 : $jj;
		$jj = ($jj <= 0 ) ? date('j') : $jj;
		$hh = sprintf('%02d',$hh);
		$hh = ($hh > 23 ) ? 23 : $hh;
		$mn = sprintf('%02d',$mn);
		$mn = ($mn > 59 ) ? 59 : $mn;
		
		$events_meta[$key . '_year'] = $aa;
		$events_meta[$key . '_month'] = $mm;
		$events_meta[$key . '_day'] = $jj;
		$events_meta[$key . '_hour'] = $hh;
		$events_meta[$key . '_minute'] = $mn;
	    $events_meta[$key . '_eventtimestamp'] = $aa . $mm . $jj . $hh . $mn;
	    
    }
    
    	// Save Locations Meta
    	
    	 $events_meta['_event_location'] = $_POST['_event_location'];	
 

	// Add values of $events_meta as custom fields

	foreach ( $events_meta as $key => $value ) { // Cycle through the $events_meta array!
		if ( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
		if ( get_post_meta( $post->ID, $key, FALSE ) ) { // If the custom field already has a value
			update_post_meta( $post->ID, $key, $value );
		} else { // If the custom field doesn't have a value
			add_post_meta( $post->ID, $key, $value );
		}
		if ( !$value ) delete_post_meta( $post->ID, $key ); // Delete if blank
	}

}

add_action( 'save_post', 'ep_eventposts_save_meta', 1, 2 );

/**
 * Helpers to display the date on the front end
 */

// Get the Month Abbreviation
 
function eventposttype_get_the_month_abbr($month) {
    global $wp_locale;
    for ( $i = 1; $i < 13; $i = $i +1 ) {
                if ( $i == $month )
                    $monthabbr = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
                }
    return $monthabbr;
}
 
// Display the date
 
function eventposttype_get_the_event_date() {
    global $post;
    $eventdate = '';
    $month = get_post_meta($post->ID, '_month', true);
    $eventdate = eventposttype_get_the_month_abbr($month);
    $eventdate .= ' ' . get_post_meta($post->ID, '_day', true) . ',';
    $eventdate .= ' ' . get_post_meta($post->ID, '_year', true);
    $eventdate .= ' at ' . get_post_meta($post->ID, '_hour', true);
    $eventdate .= ':' . get_post_meta($post->ID, '_minute', true);
    echo $eventdate;
}

// Add custom CSS to style the metabox
add_action('admin_print_styles-post.php', 'ep_eventposts_css');
add_action('admin_print_styles-post-new.php', 'ep_eventposts_css');

function ep_eventposts_css() {
	wp_enqueue_style('your-meta-box', plugin_dir_url( __FILE__ ) . '/event-post-metabox.css');
}

/**
 * Customize Event Query using Post Meta
 * 
 * @link http://www.billerickson.net/customize-the-wordpress-query/
 * @param object $query data
 *
 */
function ep_event_query( $query ) {

	// http://codex.wordpress.org/Function_Reference/current_time
	$current_time = current_time('mysql'); 
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $current_time );
	$current_timestamp = $today_year . $today_month . $today_day . $hour . $minute;

	global $wp_the_query;
	
	if ( $wp_the_query === $query && !is_admin() && is_post_type_archive( 'event' ) ) {
		$meta_query = array(
			array(
				'key' => '_start_eventtimestamp',
				'value' => $current_timestamp,
				'compare' => '>'
			)
		);
		$query->set( 'meta_query', $meta_query );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', '_start_eventtimestamp' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', '2' );
	}

}

add_action( 'pre_get_posts', 'ep_event_query' );

?>
