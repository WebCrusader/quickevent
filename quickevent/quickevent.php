<?php
/**
 * Plugin Name: Quick Event
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
{
    exit;
}

// Include DatePicker js/css scripts to administration
function quick_event_admin_custom_scripts()
{
    wp_enqueue_script('jquery-ui-datepicker');
    wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui');
}
add_action('admin_init', 'quick_event_admin_custom_scripts' );

// Register custom post Quick Event
function quick_event_custom_post_type()
{
    register_post_type('quick_event',
                       array(
                            'labels'      => array(
                               'name'          => __('Events', 'textdomain'),
                               'singular_name' => __('Event', 'textdomain'),
                            ),
                            'public'      => true,
                            'has_archive' => true,
                            'rewrite'     => array( 'slug' => 'events' ),
                       )
    );
}
add_action('init', 'quick_event_custom_post_type');

// Add admin custom fields for Quick Event
function quick_event_add_custom_box()
{
    add_meta_box(
        'quick_event_box_id', // Unique ID
        'Event fields', // Box title
        'quick_event_custom_box_html', // Content callback, must be of type callable
        'quick_event' // Post type
    );
}
add_action('add_meta_boxes', 'quick_event_add_custom_box');

// Admin custom fields content callback
function quick_event_custom_box_html($post)
{
    $date = get_post_meta($post->ID, '_quick_event_date_meta_key', true);
    ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#quick_event_date').datepicker({
        dateFormat : 'yy-mm-dd'
    });
});
</script>
    <label for="quick_event_date_field">Event Date</label>
    <input type="text" name="quick_event_date" id="quick_event_date" value="<?php echo esc_attr( $date ); ?>" />
    <?php
    $location = get_post_meta($post->ID, '_quick_event_location_meta_key', true);
    ?>
    <label for="quick_event_location_field">Event Location</label>
    <input type="text" name="quick_event_location" id="quick_event_location" value="<?php echo esc_attr( $location ); ?>" />
    <?php
    $url = get_post_meta($post->ID, '_quick_event_url_meta_key', true);
    ?>
    <label for="quick_event_url_field">Event URL</label>
    <input type="text" name="quick_event_url" id="quick_event_url" value="<?php echo esc_attr( $url ); ?>" />
    <?php
}

// Admin custom content save
function quick_event_save_postdata($post_id)
{
    if (array_key_exists('quick_event_date', $_POST)) {
        update_post_meta(
            $post_id,
            '_quick_event_date_meta_key',
            $_POST['quick_event_date']
        );
    }
    if (array_key_exists('quick_event_location', $_POST)) {
        update_post_meta(
            $post_id,
            '_quick_event_location_meta_key',
            $_POST['quick_event_location']
        );
    }
    if (array_key_exists('quick_event_url', $_POST)) {
        update_post_meta(
            $post_id,
            '_quick_event_url_meta_key',
            $_POST['quick_event_url']
        );
    }
}
add_action('save_post', 'quick_event_save_postdata');

// Customize Quick Event archive to order by date meta field
function quick_event_custom_query( $query ) {
    if ( $query->is_main_query() && is_post_type_archive( 'quick_event' ) ) {
        $query->set( 'meta_key', '_quick_event_date_meta_key' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'ASC' );
    }
}
add_filter( 'pre_get_posts', 'quick_event_custom_query' );

// Display Quick Event fields
function quick_event_the_content( $content ) {
    global $post;
    $event_fields = '';
    $date = get_post_meta($post->ID, '_quick_event_date_meta_key', true);
    if ($date) {
        $event_fields .= 'Date: ' . date( 'l jS \o\f F Y', strtotime($date) ) . '<br />';
    }
    $location = get_post_meta($post->ID, '_quick_event_location_meta_key', true);
    if ($location) {
        $event_fields .= 'Location: ' . $location . '<br />';
    }
    $url = get_post_meta($post->ID, '_quick_event_url_meta_key', true);
    if ($url) {
        $event_fields .= 'Link: <a href="' . esc_attr( $url ) . '">' . $url . '</a><br />';
    }
    if ($date) {
        $event_fields .= '<a href="http://www.google.com/calendar/render?
action=TEMPLATE
&text=' . esc_attr( $post->post_title ) . '
&dates=' . date( 'Ymd', strtotime($date) ) . '/' . date( 'Ymd', strtotime($date) + 24 * 60 * 60  ) . '
&details=' . esc_attr( $post->post_content ) . '
&location=' . esc_attr( $location ) . '
&trp=false
&sprop=
&sprop=name:"
target="_blank" rel="nofollow">Add to my calendar</a><br />';
    }
    $content = $event_fields . $content;
    return $content;
}
add_filter( 'the_excerpt', 'quick_event_the_content' );



