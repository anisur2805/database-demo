<?php

function dbdemo_assets() {
      wp_enqueue_style('dbdemo-style', DBDEMO_DIR_URL . '/css/style.css', time());
}
add_action('admin_enqueue_scripts', 'dbdemo_assets' );