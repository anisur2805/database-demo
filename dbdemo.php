<?php

/**
 * Plugin Name: Database Demo
 * Description: Awesome Desc...
 * Plugin URI:  http://github.com/database-demo
 * Version:     1.0
 * Author:      Anisur Rahman
 * Author URI:  http://github.com/anisur2805/
 * Text Domain: database-demo
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
      exit;
}

define('DBDEMO_DB_VERSION', '1.0');
$test = define("DBDEMO_DIR_URL", plugin_dir_url(__FILE__) . "assets");

require_once 'class.dbdemo.php';
require_once 'assets.php';

function dbdemo_init() {

      /**
       * Create table once the plugin activated
       */
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';
      $sql = "CREATE TABLE {$table_name} (
         id INT NOT NULL AUTO_INCREMENT,
         name VARCHAR(250),
         email VARCHAR(250),
         PRIMARY KEY (id)   
      );";

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      dbDelta($sql);

      /**
       * Set track of version 
       * if not match the current version than add new column 
       * named age 
       */
      add_option('dbdemo_db_version', DBDEMO_DB_VERSION);
      if (get_option('dbdemo_db_version' != DBDEMO_DB_VERSION)) {
            $sql = "CREATE TABLE {$table_name} (
                  id INT NOT NULL AUTO_INCREMENT,
                  name VARCHAR(250),
                  email VARCHAR(250),
                  age INT,
                  PRIMARY KEY (id)   
            );";

            dbDelta($sql);
            update_option('dbdemo_db_version', DBDEMO_DB_VERSION);
      }
}
register_activation_hook(__FILE__, 'dbdemo_init');

/**
 * dbDelta function can't handle the drop column 
 * so use manual drop query here
 */
function dbdemo_drop_column() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';

      if (get_option('dbdemo_db_version')) {
            $query = "ALTER TABLE {$table_name} DROP COLUMN IF EXISTS age";
            $wpdb->query($query);
      }
      update_option('dbdemo_db_version', DBDEMO_DB_VERSION);
}
// add_action('plugin_loaded', 'dbdemo_drop_column');

/**
 * Load some dummy data once the plugin is activated
 * 
 */
function dbdemo_load_data() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';
      $wpdb->insert("{$table_name}", [
            'name' => 'Anisur Rahaman',
            'email' => 'anisur@rahman.com',
      ]);
      $wpdb->insert("{$table_name}", [
            'name' => 'John Rahaman',
            'email' => 'john@rahman.com',
      ]);
      //    $wpdb->query()
}
register_activation_hook(__FILE__, 'dbdemo_load_data');

/**
 * empty the table when deactivate 
 * the plugin 
 * 
 */
function dbdemo_flush_data() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';
      $query = "TRUNCATE TABLE {$table_name}";
      $wpdb->query($query);
}
register_deactivation_hook(__FILE__, 'dbdemo_flush_data');

/**
 * Add Dbdemo Menu Page 
 * Toplevel 
 */
function dbdemo_admin_menu() {
      add_menu_page(__('DB Demo', 'database-demo'), __('DB Demo', 'database-demo'), 'manage_options', 'dbdemo', 'render_dbdemo_page');
}
add_action('admin_menu', 'dbdemo_admin_menu');

/**
 * Query data from db
 */
function render_dbdemo_page() {
      global $wpdb;
      if( isset( $_GET['pid'] ) ) {
            if( ! isset( $_GET['n'] ) || ! wp_verify_nonce( $_GET['n'], 'dbdemo_edit' ) ) {
                  wp_die( __('You are not authorized', 'dbdemo' ) );
            }
            
            if( isset( $_GET['action']) && $_GET['action'] == 'delete' ) {
                  $wpdb->delete( "{$wpdb->prefix}persons", ['id' => sanitize_key( $_GET['pid'] ) ] );
                  $_GET['pid'] = null;
            }
      }
      
      $id = $_GET['pid'] ?? 0;
      $id = sanitize_key($id);
      if ($id) {
            $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}persons WHERE id={$id}");
            // if ($result) {
            //       echo "Name: {$result->name}<br/>";
            //       echo "Email: {$result->email}";
            // }
      }
?>
      <div class="wrap">
            <div class="dbdemo-box-item">
                  <h2>DbDemo</h2>
                  <div class="d-none notice notice-success is-dismissible mb-10">
                        <p>Error</p>
                  </div>

                  <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('dbnonce', 'nonce'); ?>
                        <input type="hidden" name="action" value="dbdemo_admin_post_nonce" />
                        <div class="form-group">
                              Name: <input type="text" name="name" value="<?php if ($id) echo $result->name; ?>" />
                        </div>
                        <div class="form-group">
                              Email: <input type="text" name="email" value="<?php if ($id) echo $result->email; ?>" />
                        </div>
                        <?php
                        if ($id) {
                              echo '<input type="hidden" name="id" value="' . $id . '" />';
                              submit_button('Update Record');
                        } else {
                              submit_button('Add Record');
                        }
                        ?>
                  </form>
            </div>
            <div class="dbdemo-box-item">
                  <h2>Users List</h2>
                  <?php
                  
                  global $wpdb;
                  $dbdemo_users = $wpdb->get_results($wpdb->prepare("SELECT id, name, email FROM {$wpdb->prefix}persons ORDER BY id DESC"), ARRAY_A);
                  // print_r( $dbdemo_users );
                  // die();
                  // $data = array();
                  $dbdemo_user_list = new DBDEMO_USER_LIST( $dbdemo_users );
                  $dbdemo_user_list->prepare_items();
                  ?>
                  <div class="wrap">
                        <form id="art-search-form" method="POST">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                              <?php 
                                    $dbdemo_user_list->search_box( 'search', 'search_id' );
                                    $dbdemo_user_list->display();
                              ?>
                        </form>
                  </div>
            </div>
      </div>
<?php

      /**
       * Insert data to table 
       * way 1
       */
      // if( isset( $_POST['submit'] ) ) {
      //       $nonce = sanitize_text_field($_POST['nonce']);

      //       if( wp_verify_nonce( $nonce, 'dbnonce' ) ) {
      //             $name = sanitize_text_field($_POST['name']);
      //             $email = sanitize_text_field($_POST['email']);      
      //             $wpdb->insert("{$wpdb->prefix}persons", [
      //                   'name' => $name,
      //                   'email' => $email
      //             ]);
      //       } else {
      //             _e('You are not authorized', 'database-demo');
      //       }
      // }


}
/**
 * Insert data to table 
 * way 2
 */
add_action('admin_post_dbdemo_admin_post_nonce', function () {
      global $wpdb;
      $nonce = sanitize_text_field($_POST['nonce']);

      if (wp_verify_nonce($nonce, 'dbnonce')) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_text_field($_POST['email']);
            $id = sanitize_text_field($_POST['id']);

            if ($id) {
                  $wpdb->update("{$wpdb->prefix}persons", [
                        'name' => $name,
                        'email' => $email
                  ], ['id' => $id]);
                  $nonce = wp_create_nonce('dbdemo_edit');
                  wp_redirect(admin_url('admin.php?page=dbdemo&pid=' . $id.'n={$nonce}'));
            } else {
                  $wpdb->insert("{$wpdb->prefix}persons", [
                        'name' => $name,
                        'email' => $email
                  ]);
                  wp_redirect(admin_url('admin.php?page=dbdemo'));
            }
      }
});

?>