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
            
            dbDelta( $sql );
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
      
      if( get_option('dbdemo_db_version')) {
            $query = "ALTER TABLE {$table_name} DROP COLUMN age";
            $wpdb->query( $query );
      }
      update_option('dbdemo_db_version', DBDEMO_DB_VERSION);
}
add_action('plugin_loaded', 'dbdemo_drop_column');

/**
 * Load some dummy data once the plugin is activated
 * 
 */
function dbdemo_load_data() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';
      $wpdb->insert("{$table_name}",[
         'name' => 'Anisur Rahaman'   ,
         'email' => 'anisur@rahman.com',
      ]);
      $wpdb->insert("{$table_name}",[
            'name' => 'John Rahaman'   ,
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
function dbdemo_flush_data(){
      global $wpdb;
      $table_name = $wpdb->prefix . 'persons';
      $query = "TRUNCATE TABLE {$table_name}";
      $wpdb->query( $query );
}
register_deactivation_hook(__FILE__, 'dbdemo_flush_data' );

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
      echo '<h2>DbDemo</h2>';
      
      global $wpdb;
      $id = $_GET['pid'] ?? 0;
      $id = sanitize_key( $id );
      if( $id ) {
            $result = $wpdb->get_row("SELECT * FROm {$wpdb->prefix}persons WHERE id={$id}");
            if( $result ){
                  echo "Name: {$result->name}<br/>";
                  echo "Email: {$result->email}";
            }
      }
      ?>
      <form action="" method="post">
            <?php wp_nonce_field('dbnonce', 'nonce'); ?>
            Name: <input type="text" name="name" value="" /><br/>
            Email: <input type="text" name="email" value="" /><br/>
            <?php submit_button('Add Record'); ?>
      </form>
      <?php
      
      if( isset( $_POST['submit'] ) ) {
            $nonce = sanitize_text_field($_POST['nonce']);
                        
            if( wp_verify_nonce( $nonce, 'dbnonce' ) ) {
                  $name = sanitize_text_field($_POST['name']);
                  $email = sanitize_text_field($_POST['email']);      
                  $wpdb->insert("{$wpdb->prefix}persons", [
                        'name' => $name,
                        'email' => $email
                  ]);
            } else {
                  _e('You are not authorized', 'database-demo');
            }
      }
}