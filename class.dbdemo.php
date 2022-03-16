<?php


if (!class_exists('WP_List_Table')) {
      require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DBDEMO_USER_LIST extends WP_List_Table {
      private $_items;
      function __construct( $data ) {
            parent::__construct();
            $this->_items = $data;
      }
      
      public function prepare_items() {
            $columns  = $this->get_columns();
            $hidden   = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();

            $perPage     = 2;
            $currentPage = $this->get_pagenum();
            $totalItems  = count($this->_items );

            $this->set_pagination_args(array(
                  'total_items' => $totalItems,
                  'per_page'    => $perPage,
            ));

            $data = array_slice($this->_items, ($currentPage - 1) * $perPage, $perPage);
            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items           = $data;
            $this->table_data( $data );
            
      }

      public function get_columns() {
            $columns = array(
                  'cb'          => '<input type="checkbox" />',
                  'name'    => __('Name', 'dbdemo'),
                  'email'      => __('Email', 'dbdemo'),
                  'action'      => __('Action', 'dbdemo'),
            );

            return $columns;
      }

      public function column_cb($item) {
            return "<input type='checkbox' value='{$item["id"]}'/>";
      }
      
      public function column_action( $item ) {
            $link = wp_nonce_url( admin_url( '?page=dbdemo&pid='.$item['id'] ), 'dbdemo_edit', 'n' );
            return '<a href="'. $link .'">'. __('Action', 'dbdemo').'</a>';
      }

      public function column_name($item) {
            $nonce = wp_create_nonce('dbdemo_edit');
            $actions = [];
            
            $actions['edit']   = sprintf('<a href="?page=%s&pid=%s&n=%s">Edit</a>', $_REQUEST['page'], $item['id'], $nonce);
            $actions['delete']   = sprintf('<a href="?page=%s&pid=%s&n=%s&action=delete">Delete</a>', $_REQUEST['page'], $item['id'], $nonce );

            return sprintf( '<strong>%1$s</strong>%2$s', $item['name'], $this->row_actions( $actions ) );
      }

      public function get_hidden_columns() {
            return array();
      }

      public function get_sortable_columns() {
            return array(
                  'id'     => array('id', false),
                  'name' => array('name', false),
                  'email'  => array('email', false),
            );
      }

      public function get_bulk_actions() {
            $actions = [
                  'edit' => __( 'Edit', 'word-count' ),
            ];

            return $actions;
      }

      public function dbdemo_user_search($item) {
            $name       = strtolower($item['name']);
            $search_name = sanitize_text_field($_REQUEST['s']);
            $search_name = strtolower($search_name);
            if (strpos($name, $search_name) !== false) {
                  return true;
            }

            return false;
      }

      public function filter_callback($item) {
            $director = $_REQUEST['filter_s'] ? $_REQUEST['filter_s'] : 'all';
            $director = strtolower($director);

            if ('all' == $director) {
                  return true;
            } else {
                  if ($director == $item['director']) {
                        return true;
                  }
            }

            return false;
      }


      private function table_data( $data ) {
            if (isset($_REQUEST['s'])) {
                  $data = array_filter($data, array($this, 'dbdemo_user_search'));
            }

            if (isset($_REQUEST['filter_s']) && !empty($_REQUEST['filter_s'])) {
                  $data = array_filter($data, array($this, 'filter_callback'));
            }

            return $data;
      }

      public function extra_tablenav($which) {
            if ('top' == $which) {
             ?>
                  <div class="actions align-left">
                        <select name="filter_s" id="filter_s">
                              <option>All</option>
                              <option value="Sidney">Sidney Lumet</option>
                              <option value="Quentin Tarantino">Quentin Tarantino</option>
                        </select>
                        <?php submit_button(__('Filter', 'word-count'), 'button', 'submit', false); ?>
                  </div>
             <?php
            }
      }

      public function column_default($item, $column_name) {
            // switch ($column_name) {
            //       case 'id':
            //       case 'name':
            //       case 'email':
            //             return $item[$column_name];

            //       default:
            //             return print_r($item, true);
            // }
            
            return $item[$column_name];
      }

      // private function sort_data($a, $b) {
      //       $orderby = 'title';
      //       $order   = 'asc';

      //       if (!empty($_GET['orderby'])) {
      //             $orderby = $_GET['orderby'];
      //       }

      //       if (!empty($_GET['order'])) {
      //             $order = $_GET['order'];
      //       }

      //       $result = strcmp($a[$orderby], $b[$orderby]);

      //       if ($order === 'asc') {
      //             return $result;
      //       }

      //       return $result;
      // }
}
