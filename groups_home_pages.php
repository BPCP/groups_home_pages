<?php
/*
Plugin Name: Groups Home Pages
Description: Adds a database table to store group home pages
Version: 1.0
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include 'groups_home_pages_controller.php';
include 'shortcodes.php';
// include 'groups_home_pages_layout.php';
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

register_activation_hook( __FILE__, 'create_groups_home_pages_table' );



// instantiate the Group_Home_Pages_List_Table class
class Group_Home_Pages_List_Table extends WP_List_Table {
 
    // constructor
    function __construct() {
        parent::__construct( array(
            'singular' => 'Groups Home Page', // singular name of the listed records
            'plural'   => 'Groups Home Pages', // plural name of the listed records
            'ajax'     => false, // should this table support ajax?
            'screen'   => null,
        ) );
        //add_action( 'admin_post_add_record', 'add_record');
        wp_register_style('groups_home_pages', plugins_url('format.css',__FILE__ ));
        wp_enqueue_style('groups_home_pages');
    }

    // function to get data from the database
    function get_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'groups_home_pages';
        $data = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY idex" );
        $new_line = (object)null;
        $max_entry = array_push($data,(object)null)-1;
        $data[$max_entry]->id = -1;
        $data[$max_entry]->idex = "";
        $data[$max_entry]->group_id = 0;
        $data[$max_entry]->homepage_id = 0;

        return $data;
    }
    function single_row( $item ) {
        echo '<tr><form method="post" action="" name="row'.$item->id.'">';
        $this->single_row_columns( $item );
        echo '<td>';
        if ($item->id==-1){
            echo '<input type="submit" name="add" value="Add New">';
        } else {
            echo '<input type="submit" name="save" value="Save">';
            echo '<input type="submit" name="delete" value="Delete">';
        }

        echo '<input class="hidden" name = "id" value="'.$item->id.'">';
        echo '</td>';
        echo '</form></tr>';
    }

    // function to define the columns of the table
    function get_columns() {
        $columns = array(
            // 'id'            =>'Index',
            'idex'          => 'Order',
            'group_id'       => 'Group',
            'homepage_id'    => 'Homepage'
        );

        return $columns;
    }


    // function to prepare the data for display
    function prepare_items() {
        $columns = $this->get_columns();
        // Make the '' column hidden
        //$columns['id'] = false;
        $hidden = array();
        $this->_column_headers = array( $columns);
        $data = $this->get_data();
        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count( $data );
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
        $this->items = $data;
    }

    // function to display the table rows
    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'idex':
                return $this->column_order($item);
            case 'group_id':
                return $this->column_group($item);
            case 'homepage_id':
                return $this->column_home_page($item);
            case 'id':
                return'';
            default:
                return print_r( $item, true ) ;
        }
    }
    // funtion to display order selector
    function column_order($item){
        $output = sprintf( '<input type="number" min="1" name="idex" value="%s">', $item->idex);
        return $output;
    }
    // function to display the home page column as a dropdown
    function column_home_page( $item ) {
        $pages = get_pages();
        $options = '<option value="0"></option>';
        foreach ( $pages as $page ) {
            $selected = '';
            if ( $page->ID == $item->homepage_id) {
                $selected = 'selected="selected"';
            }
            $options .= sprintf( '<option value="%s" %s>%s</option>', $page->ID, $selected, $page->post_title );
        }
        return sprintf(
            '<select name="homepage_id">%s</select>',  $options
        );
    }
    
    // function to display the group column as a dropdown
    function column_group( $item) {
        global $wpdb;
        $groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}groups_group" );
        $options = '<option value="0"></option>';
        foreach ( $groups as $group ) {
            $selected = '';
            if ( $group->group_id == $item->group_id ) {
                $selected = 'selected="selected"';
            }
            $options .= sprintf( '<option value="%s" %s>%s</option>', $group->group_id, $selected, $group->name );
        }
        return sprintf(
            '<select name="group_id">%s</select>', $options
        );
    }
}
function groups_home_pages_page() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $updates = array(
            "idex" =>$_POST['idex'],
            "group_id" =>$_POST['group_id'],
            "homepage_id" =>$_POST['homepage_id']
        );
        global $wpdb;
        $table_name = $wpdb->prefix . 'groups_home_pages';
        if (isset($_POST['save'])) {
            $wpdb->update($table_name,$updates,array( 'id' => $_POST['id'] ));
        } elseif (isset($_POST['delete'])) {
         // Handle delete action
            $wpdb->delete($table_name,array( 'id' => $_POST['id'] ));
        } elseif (isset($_POST['add'])) {
            if (($_POST['group_id']>0)and($_POST['homepage_id']>0)) {
                $wpdb->insert( $table_name, $updates );
            } else {
                echo '<script>alert("You must choose both a Group and a Homepage")</script>';
            };
          
        };
    };
    $group_home_pages_list_table = new Group_Home_Pages_List_Table();
    echo '<div class="wrap"><h2>Set up Home Pages based on Group Membership</h2>';
    echo '<p>Home pages will be based on the first group the user belongs to</p>';
    echo 'Place the shortcode [group_homepage] in the designated "front page"';
    $group_home_pages_list_table->prepare_items();
    $group_home_pages_list_table->views();
    $group_home_pages_list_table->display();
    echo '</div>';
}