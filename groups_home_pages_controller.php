<?php

function create_groups_home_pages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'groups_home_pages';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idex INT NOT NULL ,
        group_id INT NOT NULL,
        homepage_id INT NOT NULL,
        INDEX idex (idex)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}


function add_group_home_pages_page() {
    add_menu_page(
        'Groups Home Pages',
        'Groups Home Pages',
        'manage_options',
        'groups_home_pages',
        'groups_home_pages_page',
        'dashicons-admin-generic'
    );
}
add_action( 'admin_menu', 'add_group_home_pages_page' );