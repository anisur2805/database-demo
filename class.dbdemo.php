<?php


if (!class_exists('WP_List_Table')) {
      require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ARTable extends WP_List_Table {
      public function prepare_items() {

            $columns  = $this->get_columns();
            $hidden   = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();

            $data = $this->table_data();
            usort($data, array(&$this, 'sort_data'));

            $perPage     = 6;
            $currentPage = $this->get_pagenum();
            $totalItems  = count($data);

            $this->set_pagination_args(array(
                  'total_items' => $totalItems,
                  'per_page'    => $perPage,
            ));

            $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items           = $data;
      }

      public function get_columns() {
            $columns = array(
                  'cb'          => '<input type="checkbox" />',
                  'id'          => 'ID',
                  'title'       => 'Title',
                  'description' => 'Desc',
                  'year'        => 'Year',
                  'director'    => 'Director',
                  'rating'      => 'Rating',
            );

            return $columns;
      }

      public function column_cb($item) {
            return "<input type='checkbox' value='{$item["id"]}'/>";
      }

      public function column_title($item) {
            $actions = [];

            $actions['edit']   = sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']);
            $actions['delete'] = sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']);

            return sprintf('<strong>%1$s</strong>%2$s', $item['title'], $this->row_actions($actions));
      }

      public function get_hidden_columns() {
            return array();
      }

      public function get_sortable_columns() {
            return array(
                  'id'     => array('id', false),
                  'rating' => array('rating', false),
                  'title'  => array('title', false),
            );
      }

      // public function get_bulk_actions() {
      //       $actions = [
      //             'edit' => __( 'Edit', 'word-count' ),
      //       ];

      //       return $actions;
      // }

      public function ar_table_search_filter($item) {
            $title       = strtolower($item['title']);
            $search_name = sanitize_text_field($_REQUEST['s']);
            $search_name = strtolower($search_name);
            if (strpos($title, $search_name) !== false) {
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


      private function table_data() {
            // require_once "datalist.php";

            // if (isset($_REQUEST['s'])) {
            //       $data = array_filter($data, array($this, 'ar_table_search_filter'));
            // }

            // if (isset($_REQUEST['filter_s']) && !empty($_REQUEST['filter_s'])) {
            //       $data = array_filter($data, array($this, 'filter_callback'));
            // }

            // return $data;
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
            switch ($column_name) {
                  case 'id':
                  case 'title':
                  case 'description':
                  case 'year':
                  case 'director':
                  case 'rating':
                        return $item[$column_name];

                  default:
                        return print_r($item, true);
            }
      }

      private function sort_data($a, $b) {
            $orderby = 'title';
            $order   = 'asc';

            if (!empty($_GET['orderby'])) {
                  $orderby = $_GET['orderby'];
            }

            if (!empty($_GET['order'])) {
                  $order = $_GET['order'];
            }

            $result = strcmp($a[$orderby], $b[$orderby]);

            if ($order === 'asc') {
                  return $result;
            }

            return $result;
      }
}
