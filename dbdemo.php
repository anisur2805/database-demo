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
