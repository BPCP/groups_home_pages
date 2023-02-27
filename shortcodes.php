<?php




function group_homepage_shortcode( ) {
    global $wpdb;
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $table_name = $wpdb->prefix . 'groups_home_pages';
    $my_groups= $wpdb->get_col( "SELECT group_id
        FROM {$wpdb->prefix}groups_user_group  
        WHERE user_id =$user_id", 0);
    $group_to_homepage = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY idex" );
    foreach ( $group_to_homepage as $group ) {
        if ( in_array( $group->group_id, $my_groups )) {
            $redirect_url= get_permalink($group->homepage_id);
            $scode_output = '<script> window.location.href = "'.$redirect_url.'" </script>';
            return $scode_output;
        }
    }
}
add_shortcode( 'group_homepage', 'group_homepage_shortcode' );
