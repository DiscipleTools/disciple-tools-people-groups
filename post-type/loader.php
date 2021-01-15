<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Add Top Navigation
 */
//add_action( 'dt_top_nav_desktop', 'dt_people_group_top_nav_desktop', 50 );
//add_action( 'dt_off_canvas_nav', 'dt_people_group_top_nav_desktop', 50 );
//function dt_people_group_top_nav_desktop() {
//    ?>
<!--    <li><a href="--><?php //echo esc_url( site_url( '/peoplegroups/' ) ); ?><!--">--><?php //esc_html_e( "People Groups", 'disciple-tools-people-groups' ); ?><!--</a></li>-->
<!--    --><?php
//}


/**
 * Test that DT_Module_Base has loaded
 */
if ( ! class_exists( 'DT_Module_Base' ) ) {
    dt_write_log( 'Disciple Tools System not loaded. Cannot load custom post type.' );
    return;
}

/**
 * Add any modules required or added for the post type
 */
add_filter( 'dt_post_type_modules', function( $modules ){
    $modules["people_groups_base"] = [
        "name" => "People Groups",
        "enabled" => true,
        "locked" => true,
        "prerequisites" => [ "contacts_base" ],
        "post_type" => "peoplegroups",
        "description" => "People Groups"
    ];
    return $modules;
}, 20, 1 );

require_once 'base-setup.php';
DT_People_Groups_Base::instance();
